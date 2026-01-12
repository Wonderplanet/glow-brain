require 'rails_helper'

RSpec.describe "MstEventStoryEpisodeTranslator" do
  subject { MstEventStoryEpisodeTranslator.translate(use_case_data) }

  context do
    let(:use_case_data) {double(:use_case_data, id: 0, episode_number: 1, name: 2, opr_event_id: 3, consume_item_amount: 4, consume_credit_amount: 5, is_movie: 6, release_at: 7)}

    it do
      view_model = subject
      expect(view_model.is_a?(MstEventStoryEpisodeViewModel)).to be_truthy
      expect(view_model.instance_values.keys.map(&:to_sym).sort == view_model.camelized_attributes.sort).to be true
      expect(view_model.id).to eq use_case_data.id
      expect(view_model.episode_number).to eq use_case_data.episode_number
      expect(view_model.name).to eq use_case_data.name
      expect(view_model.opr_event_id).to eq use_case_data.opr_event_id
      expect(view_model.consume_item_amount).to eq use_case_data.consume_item_amount
      expect(view_model.consume_credit_amount).to eq use_case_data.consume_credit_amount
      expect(view_model.is_movie).to eq use_case_data.is_movie
      expect(view_model.release_at).to eq use_case_data.release_at
    end
  end
end
