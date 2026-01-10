require 'rails_helper'

RSpec.describe "SkillTreeNodeReleaseResultTranslator" do
  subject { SkillTreeNodeReleaseResultTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, skill_tree_node_release: 0, mst_skill_tree_node_ids: 1, after_character_variant: 2, solo_story_episodes: 3, character_variant_voice: 4, after_items: 5, updated_mission: 6)}

    it do
      view_model = subject
      expect(view_model.is_a?(SkillTreeNodeReleaseResultViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.skill_tree_node_release).to eq use_case_data.skill_tree_node_release
      expect(view_model.mst_skill_tree_node_ids).to eq use_case_data.mst_skill_tree_node_ids
      expect(view_model.after_character_variant).to eq use_case_data.after_character_variant
      expect(view_model.solo_story_episodes).to eq use_case_data.solo_story_episodes
      expect(view_model.character_variant_voice).to eq use_case_data.character_variant_voice
      expect(view_model.after_items).to eq use_case_data.after_items
      expect(view_model.updated_mission).to eq use_case_data.updated_mission
    end
  end
end
