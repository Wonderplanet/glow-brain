class StepupGachaTranslator
  def self.translate(stepup_gacha_model)
    view_model = StepupGachaViewModel.new
    view_model.opr_stepup_gacha_id = stepup_gacha_model.opr_stepup_gacha_id
    view_model.current_step_number = stepup_gacha_model.current_step_number
    view_model
  end
end
