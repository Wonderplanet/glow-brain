class OprGachaTranslator
  def self.translate(opr_gacha_model)
    view_model = OprGachaViewModel.new
    view_model.id = opr_gacha_model.id
    view_model.name = opr_gacha_model.name
    view_model.asset_key = opr_gacha_model.asset_key
    view_model.start_at = opr_gacha_model.start_at
    view_model.end_at = opr_gacha_model.end_at
    view_model.sort_number = opr_gacha_model.sort_number
    view_model.banner_path = opr_gacha_model.banner_path
    view_model.special = opr_gacha_model.special
    view_model.special_caption = opr_gacha_model.special_caption
    view_model.hide_if_shortage = opr_gacha_model.hide_if_shortage
    view_model.caution = opr_gacha_model.caution
    view_model.gacha_payment_type = opr_gacha_model.gacha_payment_type
    view_model.primary_payment_amount = opr_gacha_model.primary_payment_amount
    view_model.primary_draw_count = opr_gacha_model.primary_draw_count
    view_model.primary_sr_bonus = opr_gacha_model.primary_sr_bonus
    view_model.primary_ssr_bonus = opr_gacha_model.primary_ssr_bonus
    view_model.primary_caption = opr_gacha_model.primary_caption
    view_model.primary_common_ticket_type = opr_gacha_model.primary_common_ticket_type
    view_model.secondary_payment_amount = opr_gacha_model.secondary_payment_amount
    view_model.secondary_draw_count = opr_gacha_model.secondary_draw_count
    view_model.secondary_sr_bonus = opr_gacha_model.secondary_sr_bonus
    view_model.secondary_ssr_bonus = opr_gacha_model.secondary_ssr_bonus
    view_model.secondary_caption = opr_gacha_model.secondary_caption
    view_model.secondary_common_ticket_type = opr_gacha_model.secondary_common_ticket_type
    view_model.first_play_primary_gacha_free = opr_gacha_model.first_play_primary_gacha_free
    view_model.first_play_secondary_gacha_free = opr_gacha_model.first_play_secondary_gacha_free
    view_model.primary_special_gacha_ticket_id = opr_gacha_model.primary_special_gacha_ticket_id
    view_model.secondary_special_gacha_ticket_id = opr_gacha_model.secondary_special_gacha_ticket_id
    view_model.limit_play_count = opr_gacha_model.limit_play_count
    view_model.beginner_flg = opr_gacha_model.beginner_flg
    view_model.oha_payment_amount = opr_gacha_model.oha_payment_amount
    view_model.oha_caption = opr_gacha_model.oha_caption
    view_model
  end
end
