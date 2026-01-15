class GachaPrizeSummaryTranslator
  def self.translate(gacha_prize_summary_model)
    view_model = GachaPrizeSummaryViewModel.new
    view_model.rarity_boxes = gacha_prize_summary_model.rarity_boxes
    view_model.ceiling_rarity_boxes = gacha_prize_summary_model.ceiling_rarity_boxes
    view_model
  end
end
