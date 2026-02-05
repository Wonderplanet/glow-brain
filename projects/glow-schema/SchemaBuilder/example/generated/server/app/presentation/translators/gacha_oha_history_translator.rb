class GachaOhaHistoryTranslator
  def self.translate(gacha_oha_history_model)
    view_model = GachaOhaHistoryViewModel.new
    view_model.opr_gacha_id = gacha_oha_history_model.opr_gacha_id
    view_model.reset_at = gacha_oha_history_model.reset_at
    view_model
  end
end
