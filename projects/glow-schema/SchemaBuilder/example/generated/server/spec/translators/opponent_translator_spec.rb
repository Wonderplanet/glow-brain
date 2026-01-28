require 'rails_helper'

RSpec.describe "OpponentTranslator" do
  subject { OpponentTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, mst_opponent_id: 1, mst_drop_id: 2)}

    it do
      view_model = subject
      expect(view_model.is_a?(OpponentViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.mst_opponent_id).to eq use_case_data.mst_opponent_id
      expect(view_model.mst_drop_id).to eq use_case_data.mst_drop_id
    end
  end
end
