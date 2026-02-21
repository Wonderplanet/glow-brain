class OprTutorialGachaTranslator
  def self.translate(opr_tutorial_gacha_model)
    view_model = OprTutorialGachaViewModel.new
    view_model.id = opr_tutorial_gacha_model.id
    view_model.name = opr_tutorial_gacha_model.name
    view_model.caution = opr_tutorial_gacha_model.caution
    view_model.secondary_ssr_bonus = opr_tutorial_gacha_model.secondary_ssr_bonus
    view_model
  end
end
