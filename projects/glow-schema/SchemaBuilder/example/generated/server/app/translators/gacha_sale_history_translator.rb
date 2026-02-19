class GachaSaleHistoryTranslator
  def self.translate(gacha_sale_history_model)
    view_model = GachaSaleHistoryViewModel.new
    view_model.opr_gacha_sale_id = gacha_sale_history_model.opr_gacha_sale_id
    view_model.primary_daily_free_play_count = gacha_sale_history_model.primary_daily_free_play_count
    view_model.secondary_daily_free_play_count = gacha_sale_history_model.secondary_daily_free_play_count
    view_model.reset_at = gacha_sale_history_model.reset_at
    view_model
  end
end
