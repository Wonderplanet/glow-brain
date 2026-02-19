class AutoGenerateGachaSettingTranslator
  def self.translate(auto_generate_gacha_setting_model)
    view_model = AutoGenerateGachaSettingViewModel.new
    view_model.gacha_generate_flg = auto_generate_gacha_setting_model.gacha_generate_flg
    view_model.sort_number = auto_generate_gacha_setting_model.sort_number
    view_model.with_cr_only = auto_generate_gacha_setting_model.with_cr_only
    view_model.pickup_percentage = auto_generate_gacha_setting_model.pickup_percentage
    view_model
  end
end
