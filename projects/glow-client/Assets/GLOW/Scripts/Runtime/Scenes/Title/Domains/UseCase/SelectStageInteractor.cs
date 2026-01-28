using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.Title.Domains.UseCase
{
    public class SelectStageInteractor : ISelectStageUseCase
    {
        [Inject] ISelectedStageRepository SelectedStageRepository { get; }

        public void SelectStage(MasterDataId mstStageId, MasterDataId mstAdventBattleId, ContentSeasonSystemId sysPvpSeasonId)
        {
            var selectedStageModel = new SelectedStageModel(mstStageId, mstAdventBattleId, sysPvpSeasonId);
            SelectedStageRepository.Save(selectedStageModel);
        }
    }
}
