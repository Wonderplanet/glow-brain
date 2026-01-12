class GachaTranslator
  def self.translate(gacha_model)
    view_model = GachaViewModel.new
    view_model.opr_gacha_id = gacha_model.opr_gacha_id
    view_model.play_count = gacha_model.play_count
    view_model.draw_count = gacha_model.draw_count
    view_model
  end
end
