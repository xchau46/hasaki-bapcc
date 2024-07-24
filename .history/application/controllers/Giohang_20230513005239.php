<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Giohang extends CI_Controller
{
    // Hàm khởi tạo
    function __construct()
    {
        parent::__construct();
        $this->load->model('frontend/Morder');
        $this->load->model('frontend/Mproduct');
        $this->load->model('frontend/Morderdetail');
        $this->load->model('frontend/Mcustomer');
        $this->load->model('frontend/Mcategory');
        $this->load->model('frontend/Mconfig');
        $this->load->model('frontend/Mdistrict');
        $this->load->model('frontend/Mprovince');
        $this->data['com'] = 'giohang';
    }

    public function index()
    {
        $this->data['title'] = 'Cuong-Long Bookstore - Giỏ hàng của bạn';
        $this->data['view'] = 'index';
        $this->load->view('frontend/layout', $this->data);
    }
    function check_mail()
    {
        $email = $this->input->post('email');
        if ($this->Mcustomer->customer_detail_email($email)) {
            $this->form_validation->set_message(__FUNCTION__, 'Email đã đã là thành viên, Vui lòng đăng nhập hoặc nhập Email khác !');
            return FALSE;
        }
        return TRUE;
    }
    public function info_order()
    {
        $this->load->library('session');
        $this->load->helper('string');
        $this->load->library('email');
        $this->load->library('form_validation');
        $d = getdate();
        $today = $d['year'] . "/" . $d['mon'] . "/" . $d['mday'] . " " . $d['hours'] . ":" . $d['minutes'] . ":" . $d['seconds'];
        if (!$this->session->userdata('sessionKhachHang')) {
            $this->form_validation->set_rules('email', 'Địa chỉ email', 'required|is_unique[db_customer.email]');
        }
        $this->form_validation->set_rules('phone', 'Số điện thoại', 'required');
        $this->form_validation->set_rules('name', 'Họ và tên', 'required|min_length[3]');
        $this->form_validation->set_rules('address', 'Địa chỉ', 'required');
        $this->form_validation->set_rules('city', 'Tỉnh thành', 'required');
        $this->form_validation->set_rules('DistrictId', 'Quận huyện', 'required');
        $priceShip = $this->Mconfig->config_price_ship();
        if ($this->form_validation->run() == TRUE) {
            //Tinh tien don hang
            $money = 0;
            if ($this->session->userdata('cart')) {
                $data = $this->session->userdata('cart');
                foreach ($data as $key => $value) {
                    $row = $this->Mproduct->product_detail_id($key);
                    $total = 0;
                    if ($row['price_sale'] > 0) {
                        $total = $row['price_sale'] * $value;
                    } else {
                        $total = $row['price'] * $value;
                    }
                    $money += $total;
                }
            }
            $idCustomer = null;
            if ($this->session->userdata('sessionKhachHang')) {
                $emailtemp = $this->session->userdata('email');
                $info = $this->session->userdata('sessionKhachHang');
                $idCustomer = $info['id'];
            } else {
                $emailtemp = $_POST['email'];
            }
            if (!$this->session->userdata('sessionKhachHang')) {
                $datacustomer = array(
                    'fullname' => $_POST['name'],
                    'phone' => $_POST['phone'],
                    'email' => $emailtemp,
                    'created' => $today,
                    'status' => 1,
                    'trash' => 1
                );
                $this->Mcustomer->customer_insert($datacustomer);
                $row = $this->Mcustomer->customer_detail_email($_POST['email']);
                $this->session->set_userdata('info-customer', $row);
                $info = $this->session->userdata('info-customer');
                if ($info['id']) {
                    $idCustomer = $info['id'];
                    $this->session->set_userdata('id-info-customer', $idCustomer);
                }
            }
            //kt ma giam gia
            if ($this->session->userdata('coupon_price')) {
                $coupon = $this->session->userdata('coupon_price');
                $idcoupon = $this->session->userdata('id_coupon_price');
                $amount_number_used = $this->Mconfig->get_amount_number_used($idcoupon);
                $mycoupon = array(
                    'number_used' => $amount_number_used + 1,
                );
                $this->Mconfig->coupon_update($mycoupon, $idcoupon);
            } else {
                $coupon = 0;
            }

            $provinceId = $_POST['city'];
            $districtId = $_POST['DistrictId'];
            $mydata = array(
                'orderCode' => random_string('alnum', 8),
                'customerid' => $idCustomer,
                'orderdate' => $today,
                'fullname' => $_POST['name'],
                'phone' => $_POST['phone'],
                'address' => $_POST['address'],
                'money' => $money + $priceShip - $coupon,
                'price_ship' => $priceShip,
                'coupon' => $coupon,
                'province' => $provinceId,
                'district' => $districtId,
                'trash' => 1,
                'status' => 0
            );
            if (isset($_POST['dathang'])) {
                //Insert to db_order
                $this->Morder->order_insert($mydata);


                // lưu tt đơn hàng và xóa session coupon
                $this->session->unset_userdata('id_coupon_price');
                $this->session->unset_userdata('coupon_price');

                //Insert to db_orderdetail
                $order_detail = $this->Morder->order_detail_customerid($idCustomer);
                $orderid = $order_detail['id'];
                $data = [];
                if ($this->session->userdata('cart')) {
                    $val = $this->session->userdata('cart');
                    foreach ($val as $key => $value) {
                        $row = $this->Mproduct->product_detail_id($key);
                        if ($row['price_sale'] > 0) {
                            $price = $row['price_sale'];
                        } else {
                            $price = $row['price'];
                        }
                        $data = array(
                            'orderid' => $orderid,
                            'productid' => $key,
                            'price' => $price,
                            'count' => $value,
                            'trash' => 1,
                            'status' => 1
                        );
                        $this->Morderdetail->orderdetail_insert($data);
                    }
                }
                $array_items = array('cart');
                $this->session->unset_userdata($array_items);
                redirect('/thankyou', 'refresh');
            } elseif (isset($_POST['vnpay'])) {

                $vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
                $vnp_Returnurl = "https://localhost/thankyou.php";
                $vnp_TmnCode = "WFA7TJD5"; //Mã website tại VNPAY 
                $vnp_HashSecret = "PYMSCAXVQDTPNLVWVJNWPLYEGTXLALTO"; //Chuỗi bí mật

                $vnp_TxnRef = $mydata['orderCode']; //Mã đơn hàng. Trong thực tế Merchant cần insert đơn hàng vào DB và gửi mã này sang VNPAY
                $vnp_OrderInfo = 'Noi Dung thanh toan';
                $vnp_OrderType = 'billpayment';
                $vnp_Amount = $mydata['money'] * 100;
                $vnp_Locale = 'vn';
                $vnp_BankCode = 'NCB';
                $vnp_IpAddr = $_SERVER['REMOTE_ADDR'];
                //Add Params of 2.0.1 Version
                //$vnp_ExpireDate = $_POST['txtexpire'];
                //Billing
                // $vnp_Bill_Mobile = $_POST['txt_billing_mobile'];
                // $vnp_Bill_Email = $_POST['txt_billing_email'];
                // $fullName = trim($_POST['txt_billing_fullname']);
                // if (isset($fullName) && trim($fullName) != '') {
                //     $name = explode(' ', $fullName);
                //     $vnp_Bill_FirstName = array_shift($name);
                //     $vnp_Bill_LastName = array_pop($name);
                // }
                // $vnp_Bill_Address = $_POST['txt_inv_addr1'];
                // $vnp_Bill_City = $_POST['txt_bill_city'];
                // $vnp_Bill_Country = $_POST['txt_bill_country'];
                // $vnp_Bill_State = $_POST['txt_bill_state'];
                // // Invoice
                // $vnp_Inv_Phone = $_POST['txt_inv_mobile'];
                // $vnp_Inv_Email = $_POST['txt_inv_email'];
                // $vnp_Inv_Customer = $_POST['txt_inv_customer'];
                // $vnp_Inv_Address = $_POST['txt_inv_addr1'];
                // $vnp_Inv_Company = $_POST['txt_inv_company'];
                // $vnp_Inv_Taxcode = $_POST['txt_inv_taxcode'];
                // $vnp_Inv_Type = $_POST['cbo_inv_type'];
                $inputData = array(
                    "vnp_Version" => "2.1.0",
                    "vnp_TmnCode" => $vnp_TmnCode,
                    "vnp_Amount" => $vnp_Amount,
                    "vnp_Command" => "pay",
                    "vnp_CreateDate" => date('YmdHis'),
                    "vnp_CurrCode" => "VND",
                    "vnp_IpAddr" => $vnp_IpAddr,
                    "vnp_Locale" => $vnp_Locale,
                    "vnp_OrderInfo" => $vnp_OrderInfo,
                    "vnp_OrderType" => $vnp_OrderType,
                    "vnp_ReturnUrl" => $vnp_Returnurl,
                    "vnp_TxnRef" => $vnp_TxnRef,
                    // "vnp_ExpireDate" => $vnp_ExpireDate,
                    // "vnp_Bill_Mobile" => $vnp_Bill_Mobile,
                    // "vnp_Bill_Email" => $vnp_Bill_Email,
                    // "vnp_Bill_FirstName" => $vnp_Bill_FirstName,
                    // "vnp_Bill_LastName" => $vnp_Bill_LastName,
                    // "vnp_Bill_Address" => $vnp_Bill_Address,
                    // "vnp_Bill_City" => $vnp_Bill_City,
                    // "vnp_Bill_Country" => $vnp_Bill_Country,
                    // "vnp_Inv_Phone" => $vnp_Inv_Phone,
                    // "vnp_Inv_Email" => $vnp_Inv_Email,
                    // "vnp_Inv_Customer" => $vnp_Inv_Customer,
                    // "vnp_Inv_Address" => $vnp_Inv_Address,
                    // "vnp_Inv_Company" => $vnp_Inv_Company,
                    // "vnp_Inv_Taxcode" => $vnp_Inv_Taxcode,
                    // "vnp_Inv_Type" => $vnp_Inv_Type
                );

                if (isset($vnp_BankCode) && $vnp_BankCode != "") {
                    $inputData['vnp_BankCode'] = $vnp_BankCode;
                }
                if (isset($vnp_Bill_State) && $vnp_Bill_State != "") {
                    $inputData['vnp_Bill_State'] = $vnp_Bill_State;
                }

                //var_dump($inputData);
                ksort($inputData);
                $query = "";
                $i = 0;
                $hashdata = "";
                foreach ($inputData as $key => $value) {
                    if ($i == 1) {
                        $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
                    } else {
                        $hashdata .= urlencode($key) . "=" . urlencode($value);
                        $i = 1;
                    }
                    $query .= urlencode($key) . "=" . urlencode($value) . '&';
                }

                $vnp_Url = $vnp_Url . "?" . $query;
                if (isset($vnp_HashSecret)) {
                    $vnpSecureHash =   hash_hmac('sha512', $hashdata, $vnp_HashSecret); //  
                    $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
                }
                $returnData = array(
                    'code' => '00', 'message' => 'success', 'data' => $vnp_Url
                );
                if (isset($_POST['redirect'])) {
                    header('Location: ' . $vnp_Url);
                    die();
                } else {
                    echo json_encode($returnData);
                }
                // vui lòng tham khảo thêm tại code demo
            }
        } else {
            $this->data['title'] = 'Cuong-Long Bookstore - Thông tin đơn hàng';
            $this->data['view'] = 'info-order';
            $this->load->view('frontend/layout', $this->data);
        }
    }

    public function thankyou()
    {
        if ($this->session->userdata('info-customer') || $this->session->userdata('sessionKhachHang')) {
            if ($this->session->userdata('sessionKhachHang')) {
                $val = $this->session->userdata('sessionKhachHang');
            } else {
                $val = $this->session->userdata('info-customer');
            }
            $list = $this->Morder->order_detail_customerid($val['id']);
            $data = array(
                'order' => $list,
                'customer' => $val,
                'orderDetail' => $this->Morderdetail->orderdetail_order_join_product($list['id']),
                'province' => $this->Mprovince->province_name($list['province']),
                'district' => $this->Mdistrict->district_name($list['district']),
                'priceShip' => $this->Mconfig->config_price_ship(),
                'coupon' => $list['coupon'],

            );
            $this->data['customer'] = $val;
            $this->data['get'] = $list;
            $this->load->library('email');
            $this->load->library('parser');
            $this->email->clear();
            $config['protocol']    = 'smtp';
            $config['smtp_host']    = 'ssl://smtp.gmail.com';
            $config['smtp_port']    = '465';
            $config['smtp_timeout'] = '7';
            $config['smtp_user']    = 'vuvancuongiuh@gmail.com';
            $config['smtp_pass']    = 'qjxxqmpecmkxtspp';
            // mk trên la mat khau dung dung cua gmail, có thể dùng gmail hoac mat khau. Tao mat khau ung dung de bao mat tai khoan
            $config['charset']    = 'utf-8';
            $config['newline']    = "\r\n";
            $config['wordwrap'] = TRUE;
            $config['mailtype'] = 'html';
            $config['validation'] = TRUE;
            $this->email->initialize($config);
            $this->email->from('vuvancuongiuh@gmail.com', 'Cuong-Long Bookstore');
            $list = array($val['email']);
            $this->email->to($list);
            $this->email->subject('Hệ thống Cuong-Long Bookstore');
            $body = $this->load->view('frontend/modules/email', $data, TRUE);
            $this->email->message($body);
            $this->email->send();

            $datax = array('email' => '');
            $idx = $this->session->userdata('id-info-customer');
            $this->Mcustomer->customer_update($datax, $idx);
            $this->session->unset_userdata('id-info-customer', 'money_check_coupon');
        }
        $this->data['title'] = 'Cuong-Long Bookstore.vn - Kết quả đơn hàng';
        $this->data['view'] = 'thankyou';
        $this->load->view('frontend/layout', $this->data);
    }

    public function district()
    {
        $this->load->library('session');
        $provinceid = $this->input->post('provinceid');
        $districts = $this->Mdistrict->district_provinceid($provinceid);
        if (count($districts) > 0) {
            $district_box = '';
            $district_box .= '<option value="">--Chon Quan Huyen--</option>';
            foreach ($districts as $district) {
                $district_box .= '<option value="' . $district['id'] . '">' . $district['name'] . '</option>';
            }
            echo json_encode($district_box);
        }
    }

    public function coupon()
    {
        $d = getdate();
        $today = $d['year'] . "-" . $d['mon'] . "-" . $d['mday'];
        $html = '';
        if ($this->session->userdata('coupon_price')) {
            $html .= '<p>Mỗi đơn hàng chỉ áp dụng 1 Mã giảm giá !!</p>';
        } else {
            if (empty($_POST['code'])) {
                $html .= '<p>Vui lòng nhập Mã giảm giá nếu có !!</p>';
            } else {
                // KIỂM TRA SỐ TIỀN TRONG GIỎ HÀNG
                $money = 0;
                if ($this->session->userdata('cart')) {
                    $data = $this->session->userdata('cart');
                    foreach ($data as $key => $value) {
                        $row = $this->Mproduct->product_detail_id($key);
                        $total = 0;
                        if ($row['price_sale'] > 0) {
                            $total = $row['price_sale'] * $value;
                        } else {
                            $total = $row['price'] * $value;
                        }
                        $money += $total;
                    }
                }
                //
                // KIỂM TRA MÃ GIẢM GIÁ CÓ TỒN TẠI KO
                $coupon = $_POST['code'];
                $getcoupon = $this->Mconfig->get_config_coupon_discount($coupon);
                if (empty($getcoupon)) {
                    $html .= '<p>Mã giảm giá không tồn tại!</p>';
                }
                foreach ($getcoupon as $value) {
                    if ($value['code'] == $coupon) {
                        if (strtotime($value['expiration_date']) <= strtotime($today)) {
                            $html .= '<p>Mã giảm giá ' . $value['code'] . ' đã hết hạn sử dụng từ ngày ' . $value['expiration_date'] . ' !</p>';
                        } else if ($value['limit_number'] - $value['number_used'] == 0) {
                            $html .= '<p>Mã giảm giá ' . $value['code'] . ' đã hết số lần nhập !</p>';
                        } else if ($value['payment_limit'] >= $money) {
                            $html .= '<p> Mã giảm giá này chỉ áp dụng cho đơn hàng từ ' . number_format($value['payment_limit']) . ' đ trở lên !</p>';
                        } else {
                            $html .= '<script>document.location.reload(true);</script> <p>Mã giảm giá ' . $value['code'] . ' đã được kích hoạt !</p>';
                            $this->session->set_userdata('coupon_price', $value['discount']);
                            $this->session->set_userdata('id_coupon_price', $value['id']);
                        }
                    }
                }
            }
        }
        echo json_encode($html);
    }
    public function removecoupon()
    {
        $html = '<script>document.location.reload(true);</script>';
        $this->session->unset_userdata('coupon_price');
        $this->session->unset_userdata('id_coupon_price');
        echo json_encode($html);
    }
}
// email trang thankyou bị sai
