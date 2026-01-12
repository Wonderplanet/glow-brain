class OprGachaSaleTranslator
  def self.translate(opr_gacha_sale_model)
    view_model = OprGachaSaleViewModel.new
    view_model.id = opr_gacha_sale_model.id
    view_model.opr_gacha_id = opr_gacha_sale_model.opr_gacha_id
    view_model.start_at = opr_gacha_sale_model.start_at
    view_model.end_at = opr_gacha_sale_model.end_at
    view_model.primary_daily_free_play_count = opr_gacha_sale_model.primary_daily_free_play_count
    view_model.secondary_daily_free_play_count = opr_gacha_sale_model.secondary_daily_free_play_count
    view_model
  end
end
