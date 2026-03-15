class GachaPrizeTranslator
  def self.translate(gacha_prize_model)
    view_model = GachaPrizeViewModel.new
    view_model.prize = gacha_prize_model.prize
    view_model.percentage = gacha_prize_model.percentage
    view_model.pickup = gacha_prize_model.pickup
    view_model
  end
end
