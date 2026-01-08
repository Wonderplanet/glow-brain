using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using Zenject;

namespace GLOW.Debugs.Home.Domain.UseCases
{
    public class GetAllStagesDebugUseCase
    {
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IMstQuestDataRepository MstQuestDataRepository { get; }

        public IReadOnlyList<DebugStageUseCaseModel> GetAllStages()
        {
            var mstQuests = MstQuestDataRepository.GetMstQuestModels();
            return MstStageDataRepository.GetMstStages()
                .GroupJoin(
                    mstQuests,
                    stage => stage.MstQuestId,
                    quest => quest.Id,
                    (stage, quests) =>
                    {
                        var difficulty = quests.Any() ? quests.First().Difficulty : Difficulty.Normal;
                        return new DebugStageUseCaseModel(stage, difficulty);
                    })
                .ToList();
        }
    }
}
