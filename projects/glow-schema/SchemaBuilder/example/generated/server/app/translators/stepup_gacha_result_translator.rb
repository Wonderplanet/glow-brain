class StepupGachaResultTranslator
  def self.translate(stepup_gacha_result_model)
    view_model = StepupGachaResultViewModel.new
    view_model.received = stepup_gacha_result_model.received
    view_model.received_histories = stepup_gacha_result_model.received_histories
    view_model.stepup_gacha = stepup_gacha_result_model.stepup_gacha
    view_model
  end
end
