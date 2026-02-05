class MstSkillTreeNodeRequiredItemTranslator
  def self.translate(mst_skill_tree_node_required_item_model)
    view_model = MstSkillTreeNodeRequiredItemViewModel.new
    view_model.id = mst_skill_tree_node_required_item_model.id
    view_model.mst_skill_tree_node_id = mst_skill_tree_node_required_item_model.mst_skill_tree_node_id
    view_model.mst_item_id = mst_skill_tree_node_required_item_model.mst_item_id
    view_model.amount = mst_skill_tree_node_required_item_model.amount
    view_model
  end
end
