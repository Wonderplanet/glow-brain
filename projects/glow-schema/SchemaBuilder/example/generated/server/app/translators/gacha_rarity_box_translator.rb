class GachaRarityBoxTranslator
  def self.translate(gacha_rarity_box_model)
    view_model = GachaRarityBoxViewModel.new
    view_model.gacha_prizes = gacha_rarity_box_model.gacha_prizes
    view_model.percentage = gacha_rarity_box_model.percentage
    view_model.rarity_type = gacha_rarity_box_model.rarity_type
    view_model
  end
end
