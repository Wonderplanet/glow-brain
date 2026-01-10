require 'rails_helper'

RSpec.describe "MstSkillTreeItemOrderTranslator" do
  subject { MstSkillTreeItemOrderTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, mst_item_id: 1, sort_order: 2)}

    it do
      view_model = subject
      expect(view_model.is_a?(MstSkillTreeItemOrderViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.mst_item_id).to eq use_case_data.mst_item_id
      expect(view_model.sort_order).to eq use_case_data.sort_order
    end
  end
end
