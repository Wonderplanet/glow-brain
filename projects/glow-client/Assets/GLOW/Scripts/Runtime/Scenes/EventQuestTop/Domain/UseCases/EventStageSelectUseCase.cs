using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.EventQuestTop.Domain.UseCases
{
    public class EventStageSelectUseCase
    {
        [Inject] IPreferenceRepository PreferenceRepository { get; }
        public void SaveLastPlayedStageInfo(MasterDataId mstQuestGroupId, MasterDataId mstStageId)
        {
            PreferenceRepository.SetLastPlayedEventAtMstQuestId(mstQuestGroupId, mstStageId);
        }
    }
}