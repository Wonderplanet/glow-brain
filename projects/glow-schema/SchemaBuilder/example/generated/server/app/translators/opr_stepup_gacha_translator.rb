class OprStepupGachaTranslator
  def self.translate(opr_stepup_gacha_model)
    view_model = OprStepupGachaViewModel.new
    view_model.id = opr_stepup_gacha_model.id
    view_model.name = opr_stepup_gacha_model.name
    view_model.asset_key = opr_stepup_gacha_model.asset_key
    view_model.start_at = opr_stepup_gacha_model.start_at
    view_model.end_at = opr_stepup_gacha_model.end_at
    view_model.sort_number = opr_stepup_gacha_model.sort_number
    view_model.banner_path = opr_stepup_gacha_model.banner_path
    view_model.stepup_gacha_payment_type = opr_stepup_gacha_model.stepup_gacha_payment_type
    view_model
  end
end
