require 'rails_helper'

RSpec.describe "OprItemTranslator" do
  subject { OprItemTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, mst_item_id: 0, expire_at: 1)}

    it do
      view_model = subject
      expect(view_model.is_a?(OprItemViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.mst_item_id).to eq use_case_data.mst_item_id
      expect(view_model.expire_at).to eq use_case_data.expire_at
    end
  end
end
