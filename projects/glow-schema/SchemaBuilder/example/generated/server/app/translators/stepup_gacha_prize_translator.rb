class StepupGachaPrizeTranslator
  def self.translate(stepup_gacha_prize_model)
    view_model = StepupGachaPrizeViewModel.new
    view_model.prize = stepup_gacha_prize_model.prize
    view_model.percentage = stepup_gacha_prize_model.percentage
    view_model.pickup = stepup_gacha_prize_model.pickup
    view_model
  end
end
