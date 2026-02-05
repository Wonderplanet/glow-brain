using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.OutpostEnhance;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.ModelFactories
{
    public class OutpostEnhancementModelFactory : IOutpostEnhancementModelFactory
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstOutpostEnhanceDataRepository MstOutpostEnhanceDataRepository { get; }

        public OutpostEnhancementModel Create()
        {
            return CreateInternal(false);
        }

        public OutpostEnhancementModel CreateForTutorialBattle()
        {
            return CreateInternal(true);
        }

        public OutpostEnhancementModel CreateOpponent(IReadOnlyList<UserOutpostEnhanceModel> userOutpostEnhanceList)
        {
            if (userOutpostEnhanceList.Count <= 0)
            {
                return OutpostEnhancementModel.Empty;
            }

            // 最初の要素からMstOutpostModelを先に取得
            var enhance = userOutpostEnhanceList.First();
            var outpostModel = MstOutpostEnhanceDataRepository.GetOutpostModel(enhance.MstOutpostId);

            var enhancementList = new List<OutpostEnhancementElement>();
            foreach (var enhancementModel in outpostModel.EnhancementModels)
            {
                var element = GetEnhanceElement(userOutpostEnhanceList, enhancementModel);
                enhancementList.Add(element);
            }

            return new OutpostEnhancementModel(enhancementList);
        }

        OutpostEnhancementModel CreateInternal(bool overrideForTutorial)
        {
            var enhancementList = new List<OutpostEnhancementElement>();

            var userOutpostList = GameRepository.GetGameFetchOther().UserOutpostModels;
            var userOutpostEnhanceList = GameRepository.GetGameFetchOther().UserOutpostEnhanceModels;

            var usedOutpostId = userOutpostList.FirstOrDefault(userOutpost => userOutpost.IsUsed)?.MstOutpostId ??
                new MasterDataId(OutpostDefaultParameterConst.DefaultOutpostId);

            var outpostModel = MstOutpostEnhanceDataRepository.GetOutpostModel(usedOutpostId);

            foreach (var enhancementModel in outpostModel.EnhancementModels)
            {
                var element = GetEnhanceElement(userOutpostEnhanceList, enhancementModel);
                enhancementList.Add(element);
            }

            if (overrideForTutorial)
            {
                enhancementList = OverrideEnhancementForTutorial(outpostModel, enhancementList);
            }

            return new OutpostEnhancementModel(enhancementList);
        }

        List<OutpostEnhancementElement> OverrideEnhancementForTutorial(
            MstOutpostModel outpostModel,
            IReadOnlyList<OutpostEnhancementElement> enhancementElements)
        {
            var overriddenEnhancementElements = enhancementElements;

            // リーダーP速度
            var leaderPointSpeedModel = outpostModel.EnhancementModels.FirstOrDefault(
                m => m.Type == OutpostEnhancementType.LeaderPointSpeed,
                MstOutpostEnhancementModel.Empty);

            if (!leaderPointSpeedModel.IsEmpty())
            {
                var maxLevelModel = leaderPointSpeedModel.Levels.MaxBy(m => m.Level);

                var newElement = new OutpostEnhancementElement(
                    OutpostEnhancementType.LeaderPointSpeed,
                    maxLevelModel.Level,
                    maxLevelModel.EnhanceValue);

                overriddenEnhancementElements = overriddenEnhancementElements.ReplaceOrAdd(
                    e => e.Type == OutpostEnhancementType.LeaderPointSpeed,
                    newElement);
            }

            // リーダーP上限
            var leaderPointLimitModel = outpostModel.EnhancementModels.FirstOrDefault(
                m => m.Type == OutpostEnhancementType.LeaderPointLimit,
                MstOutpostEnhancementModel.Empty);

            if (!leaderPointLimitModel.IsEmpty())
            {
                var maxLevelModel = leaderPointLimitModel.Levels.MaxBy(m => m.Level);

                var newElement = new OutpostEnhancementElement(
                    OutpostEnhancementType.LeaderPointLimit,
                    maxLevelModel.Level,
                    maxLevelModel.EnhanceValue);

                overriddenEnhancementElements = overriddenEnhancementElements.ReplaceOrAdd(
                    e => e.Type == OutpostEnhancementType.LeaderPointLimit,
                    newElement);
            }

            return overriddenEnhancementElements.ToList();
        }

        OutpostEnhancementElement GetEnhanceElement(
            IReadOnlyList<UserOutpostEnhanceModel> userOutpostEnhanceList,
            MstOutpostEnhancementModel mstOutpostEnhanceModel)
        {
            var userOutpostEnhanceModel = userOutpostEnhanceList
                .Where(enhance => enhance.MstOutpostId == mstOutpostEnhanceModel.OutpostId)
                .FirstOrDefault(
                    enhance => enhance.MstOutpostEnhanceId == mstOutpostEnhanceModel.Id,
                    UserOutpostEnhanceModel.Empty);

            var currentLevel = userOutpostEnhanceModel.IsEmpty()
                ? OutpostEnhanceLevel.One
                : userOutpostEnhanceModel.Level;

            var mstOutpostEnhancementLevelModel =
                mstOutpostEnhanceModel.Levels.Find(level => level.Level == currentLevel);

            return new OutpostEnhancementElement(
                mstOutpostEnhanceModel.Type,
                mstOutpostEnhancementLevelModel.Level,
                mstOutpostEnhancementLevelModel.EnhanceValue);
        }
    }
}
