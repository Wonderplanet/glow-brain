class OprStepupGachaStepTranslator
  def self.translate(opr_stepup_gacha_step_model)
    view_model = OprStepupGachaStepViewModel.new
    view_model.opr_stepup_gacha_id = opr_stepup_gacha_step_model.opr_stepup_gacha_id
    view_model.step_number = opr_stepup_gacha_step_model.step_number
    view_model.primary_payment_amount = opr_stepup_gacha_step_model.primary_payment_amount
    view_model.primary_draw_count = opr_stepup_gacha_step_model.primary_draw_count
    view_model.description = opr_stepup_gacha_step_model.description
    view_model
  end
end
