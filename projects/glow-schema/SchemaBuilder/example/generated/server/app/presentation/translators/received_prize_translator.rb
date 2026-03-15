class ReceivedPrizeTranslator
  def self.translate(received_prize_model)
    view_model = ReceivedPrizeViewModel.new
    view_model.items = received_prize_model.items
    view_model.crystal = received_prize_model.crystal
    view_model.character_variants = received_prize_model.character_variants
    view_model.sent_present_box_flg = received_prize_model.sent_present_box_flg
    view_model
  end
end
