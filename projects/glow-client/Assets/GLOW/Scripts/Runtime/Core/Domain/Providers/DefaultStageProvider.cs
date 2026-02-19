using System;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using Zenject;

namespace GLOW.Core.Domain.Providers
{
    public class DefaultStageProvider : IDefaultStageProvider
    {
        [Inject] IMstStageDataRepository MstStageDataRepository { get; }
        [Inject] IMstQuestDataRepository MstQuestDataRepository { get; }

        public MstStageModel GetDefaultStage()
        {
            // すべてのステージを取得してSortOrderでソート
            var allStages = MstStageDataRepository
                .GetMstStages()
                .OrderBy(stage => stage.SortOrder);
            
            // SortOrderが小さいステージから、QuestTypeがNormalのステージを探す
            foreach (var stage in allStages)
            {
                var quest = MstQuestDataRepository.GetMstQuestModel(stage.MstQuestId);
                if (quest.QuestType == QuestType.Normal)
                {
                    return stage;
                }
            }

            throw new Exception("No default stage found");
        }
    }
}