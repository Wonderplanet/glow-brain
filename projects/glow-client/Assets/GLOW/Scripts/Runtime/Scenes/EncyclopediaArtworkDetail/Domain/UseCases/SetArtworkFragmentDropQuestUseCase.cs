using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using Zenject;

namespace GLOW.Scenes.EncyclopediaArtworkDetail.Domain.UseCases
{
    public class SetArtworkFragmentDropQuestUseCase
    {
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IMstQuestDataRepository MstQuestDataRepository { get; }
        [Inject] IPreferenceRepository PreferenceRepository { get; }
        [Inject] IMstArtworkFragmentDataRepository MstArtworkFragmentDataRepository { get; }
        [Inject] ISelectedStageRepository SelectedStageRepository { get; }

        public void SetSelectedStage(MasterDataId mstArtworkFragmentId)
        {
            var mstArtworkFragment = MstArtworkFragmentDataRepository.GetArtworkFragment(mstArtworkFragmentId);
            var mstStage = MstStageDataRepository.GetMstStages()
                .Find(stage => stage.MstArtworkFragmentDropGroupId == mstArtworkFragment.MstDropGroupId);

            var mstQuest = MstQuestDataRepository.GetMstQuestModel(mstStage.MstQuestId);
            if (mstQuest.QuestType == QuestType.Event)
            {
                PreferenceRepository.SetLastPlayedEventAtMstQuestId(mstQuest.GroupId, mstStage.Id);
            }
            else
            {
                PreferenceRepository.SetCurrentHomeTopSelectMstQuestId(mstStage.MstQuestId);
            }

            var selectedStageModel = new SelectedStageModel(mstStage.Id, MasterDataId.Empty, ContentSeasonSystemId.Empty);
            SelectedStageRepository.Save(selectedStageModel);
        }
    }
}
