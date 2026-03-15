class ReceivedPrizeHistoryTranslator
  def self.translate(received_prize_history_model)
    view_model = ReceivedPrizeHistoryViewModel.new
    view_model.add_item_history = received_prize_history_model.add_item_history
    view_model.add_crystal = received_prize_history_model.add_crystal
    view_model.add_character_variant_history = received_prize_history_model.add_character_variant_history
    view_model
  end
end
