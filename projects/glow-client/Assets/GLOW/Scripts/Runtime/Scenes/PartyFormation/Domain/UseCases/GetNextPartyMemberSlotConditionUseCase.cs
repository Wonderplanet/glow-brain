using System.Linq;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Stage;
using GLOW.Core.Extensions;
using GLOW.Scenes.PartyFormation.Domain.Models;
using Zenject;

namespace GLOW.Scenes.PartyFormation.Domain.UseCases
{
    public class GetNextPartyMemberSlotConditionUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstPartyUnitCountDataRepository MstPartyUnitCountDataRepository { get; }
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IMstQuestDataRepository MstQuestDataRepository { get; }

        public PartyFormationMemberNextUnlockSlotModel GetNextPartyMemberSlotCondition()
        {
            var gameFetch = GameRepository.GetGameFetch();
            var userStages = gameFetch.StageModels;
            var mstPartyUnitCounts = MstPartyUnitCountDataRepository.GetPartyUnitCounts();

            var lockUnitCount = mstPartyUnitCounts
                .Where(mst => !mst.MstStageId.IsEmpty()
                    && userStages.All(stage => stage.ClearCount.Value == 0 || stage.MstStageId != mst.MstStageId))
                .ToList();

            if (lockUnitCount.Count == 0)
            {
                return PartyFormationMemberNextUnlockSlotModel.Empty;
            }

            var nextUnlock = lockUnitCount
                .OrderBy(mst => mst.Count)
                .First();

            var mstStage = MstStageDataRepository.GetMstStage(nextUnlock.MstStageId);
            var mstQuest = MstQuestDataRepository.GetMstQuestModel(mstStage.MstQuestId);

            return new PartyFormationMemberNextUnlockSlotModel(mstQuest.Name, mstQuest.Difficulty, mstStage.StageNumber, nextUnlock.Count);
        }

        public PartyFormationMemberNextUnlockSlotModel GetNextPartyMemberSlotCondition(PartyMemberSlotCount targetSlot)
        {
            var gameFetch = GameRepository.GetGameFetch();
            var userStages = gameFetch.StageModels;
            var mstPartyUnitCounts = MstPartyUnitCountDataRepository.GetPartyUnitCounts();

            var nextUnlock = mstPartyUnitCounts.Find(mst => mst.Count == targetSlot);

            var mstStage = MstStageDataRepository.GetMstStage(nextUnlock.MstStageId);
            var mstQuest = MstQuestDataRepository.GetMstQuestModel(mstStage.MstQuestId);

            return new PartyFormationMemberNextUnlockSlotModel(mstQuest.Name, mstQuest.Difficulty, mstStage.StageNumber, nextUnlock.Count);
        }
    }
}
