require 'rails_helper'

RSpec.describe "MstSoloLiveDropTranslator" do
  subject { MstSoloLiveDropTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, group_number: 0, relative_probability: 1, mst_item_id: 2, amount: 3)}

    it do
      view_model = subject
      expect(view_model.is_a?(MstSoloLiveDropViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.group_number).to eq use_case_data.group_number
      expect(view_model.relative_probability).to eq use_case_data.relative_probability
      expect(view_model.mst_item_id).to eq use_case_data.mst_item_id
      expect(view_model.amount).to eq use_case_data.amount
    end
  end
end
