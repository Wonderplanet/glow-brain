using System.Linq;
using GLOW.Core.Domain.Repositories;
using Zenject;

namespace GLOW.Scenes.Home.Domain.UseCases
{
    public class UpdatePartyMemberSlotUseCase
    {
        [Inject] IPartyCacheRepository PartyCacheRepository { get; }
        [Inject] IMstPartyUnitCountDataRepository MstPartyUnitCountDataRepository { get; }
        [Inject] IGameRepository GameRepository { get; }

        public void UpdatePartyMemberSlot()
        {
            var gameFetch = GameRepository.GetGameFetch();

            var mstPartyUnitCounts = MstPartyUnitCountDataRepository.GetPartyUnitCounts();
            var enablePartyUnitCount = mstPartyUnitCounts
                .Where(mst =>
                    mst.MstStageId.IsEmpty()
                    || gameFetch.StageModels.Any(stage => stage.MstStageId == mst.MstStageId && stage.ClearCount.Value > 0))
                .OrderByDescending(mst => mst.Count)
                .First();
            PartyCacheRepository.SetPartyMemberSlotCount(enablePartyUnitCount.Count);
        }
    }
}
