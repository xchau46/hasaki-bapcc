<select name="DistrictId" id="district" class="form-control next-select">
    <option value="">--- Chọn quận huyện ---</option>
    <?php
    if (!empty($list)) {
        foreach ($list as $value) {
    ?>
            <option value="<?php echo $value['id']; ?>"><?php echo $value['name']; ?></option>
    <?php
        }
    }
    ?>
</select>