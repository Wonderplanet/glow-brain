class PrizeTranslator
  def self.translate(prize_model)
    view_model = PrizeViewModel.new
    view_model.prize_type = prize_model.prize_type
    view_model.mst_character_variant_id = prize_model.mst_character_variant_id
    view_model.mst_item_id = prize_model.mst_item_id
    view_model.item_amount = prize_model.item_amount
    view_model.crystal_amount = prize_model.crystal_amount
    view_model
  end
end
