using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Home.Domain.UseCases;
using Zenject;

namespace GLOW.Scenes.GameModeSelect.Domain
{
    public class UpdateCurrentMstQuestIdUseCase
    {
        [Inject] HomeCurrentQuestSelectFactory HomeCurrentQuestSelectFactory { get; }
        [Inject] IPreferenceRepository PreferenceRepository { get; }
        public void UpdateCurrentMstQuestId(GameModeType gameModeType, MasterDataId mstEventId)
        {
            var updatedMstQuestId = HomeCurrentQuestSelectFactory.GetHomeBackgroundMstQuestId(gameModeType.ToQuestType(), mstEventId);
            PreferenceRepository.SetCurrentHomeTopSelectMstQuestId(updatedMstQuestId);
        }
    }
}
