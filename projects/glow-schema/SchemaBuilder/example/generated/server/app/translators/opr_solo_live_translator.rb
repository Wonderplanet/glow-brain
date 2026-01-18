class OprSoloLiveTranslator
  def self.translate(opr_solo_live_model)
    view_model = OprSoloLiveViewModel.new
    view_model.id = opr_solo_live_model.id
    view_model.mst_character_variant_id = opr_solo_live_model.mst_character_variant_id
    view_model.start_at = opr_solo_live_model.start_at
    view_model.end_at = opr_solo_live_model.end_at
    view_model.asset_key = opr_solo_live_model.asset_key
    view_model.mst_live_house_id = opr_solo_live_model.mst_live_house_id
    view_model.default_mst_music_id = opr_solo_live_model.default_mst_music_id
    view_model.mst_drop_group_number = opr_solo_live_model.mst_drop_group_number
    view_model.rank_exp = opr_solo_live_model.rank_exp
    view_model.tp = opr_solo_live_model.tp
    view_model.stamina_consumption = opr_solo_live_model.stamina_consumption
    view_model.gacha_setting = opr_solo_live_model.gacha_setting
    view_model.opr_gacha_id = opr_solo_live_model.opr_gacha_id
    view_model
  end
end
