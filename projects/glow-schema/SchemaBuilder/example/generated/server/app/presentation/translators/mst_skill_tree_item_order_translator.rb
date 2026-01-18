class MstSkillTreeItemOrderTranslator
  def self.translate(mst_skill_tree_item_order_model)
    view_model = MstSkillTreeItemOrderViewModel.new
    view_model.id = mst_skill_tree_item_order_model.id
    view_model.mst_item_id = mst_skill_tree_item_order_model.mst_item_id
    view_model.sort_order = mst_skill_tree_item_order_model.sort_order
    view_model
  end
end
