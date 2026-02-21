class ExtendedPrizeTranslator
  def self.translate(extended_prize_model)
    view_model = ExtendedPrizeViewModel.new
    view_model.extended_prize_type = extended_prize_model.extended_prize_type
    view_model.mst_character_variant_id = extended_prize_model.mst_character_variant_id
    view_model.mst_item_id = extended_prize_model.mst_item_id
    view_model.item_amount = extended_prize_model.item_amount
    view_model.crystal_amount = extended_prize_model.crystal_amount
    view_model.mst_event_story_episode_id = extended_prize_model.mst_event_story_episode_id
    view_model
  end
end
