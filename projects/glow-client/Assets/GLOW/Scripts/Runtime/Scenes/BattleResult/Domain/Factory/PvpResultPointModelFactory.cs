using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.Pvp;
using GLOW.Core.Extensions;
using GLOW.Scenes.PvpBattleResult.Domain.Model;
using Zenject;

namespace GLOW.Scenes.BattleResult.Domain.Factory
{
    public class PvpResultPointModelFactory : IPvpResultPointModelFactory
    {
        [Inject] IMstPvpDataRepository MstPvpDataRepository { get; }
        
        PvpBattleResultPointModel IPvpResultPointModelFactory.CreatePvpResultPointModel(
            PvpPoint beforePvpPoint,
            PvpPoint afterPvpPoint,
            PvpEndResultBonusPointModel resultBonusPointModel)
        {
            if (beforePvpPoint <= afterPvpPoint)
            {
                return CreatePvpRankUpResultPointModel(
                    beforePvpPoint,
                    afterPvpPoint,
                    resultBonusPointModel);
            }
            else
            {
                return CreatePvpRankDownResultPointModel(
                    beforePvpPoint,
                    afterPvpPoint,
                    resultBonusPointModel);
            }
        }

        PvpBattleResultPointModel CreatePvpRankUpResultPointModel(
            PvpPoint beforePvpPoint,
            PvpPoint afterPvpPoint,
            PvpEndResultBonusPointModel resultBonusPointModel)
        {
            var mstPvpRankModels = MstPvpDataRepository.GetMstPvpRanks()
                .OrderBy(model => model.RequiredLowerPoint)
                .ToList();
            
            var beforeRankModel = MstPvpRankModel.Empty;
            var beforeRankModelIndex = -1;
            for (var i = 0; i < mstPvpRankModels.Count; i++)
            {
                var model = mstPvpRankModels[i];
                if (model.RequiredLowerPoint <= beforePvpPoint)
                {
                    // 加算前のランクモデルを取得
                    beforeRankModel = model;
                    beforeRankModelIndex = i;
                }
                else
                {
                    // 加算前のランクの次のランクの情報までを取得
                    break;
                }
            }
            
            var updatedRankModelIndex = -1;
            for (var i = 0; i < mstPvpRankModels.Count; i++)
            {
                var model = mstPvpRankModels[i];
                if (model.RequiredLowerPoint > afterPvpPoint)
                {
                    // 加算後のランクの次のランクの情報までを取得
                    updatedRankModelIndex = i;
                    break;
                }
                
                if (i == mstPvpRankModels.Count - 1)
                {
                    updatedRankModelIndex = i;
                }
            }
            
            // ランクが上がった場合、加算前のランクと加算後のランクの間のランクモデルを取得
            var pvpResultPointRankTargetModels = CreatePointUppedRankTargetModels(
                beforePvpPoint,
                afterPvpPoint,
                beforeRankModel,
                beforeRankModelIndex,
                updatedRankModelIndex,
                mstPvpRankModels);

            return new PvpBattleResultPointModel(
                beforeRankModel.RankClassType,
                beforeRankModel.RankLevel,
                pvpResultPointRankTargetModels,
                resultBonusPointModel.ResultPoint,
                resultBonusPointModel.OpponentBonusPoint,
                resultBonusPointModel.TimeBonusPoint,
                afterPvpPoint);
        }

        IReadOnlyList<PvpBattleResultPointRankTargetModel> CreatePointUppedRankTargetModels(
            PvpPoint beforePvpPoint,
            PvpPoint afterPvpPoint,
            MstPvpRankModel beforeRankModel,
            int beforeRankModelIndex,
            int updatedRankModelIndex,
            IReadOnlyList<MstPvpRankModel> mstPvpRankModels)
        {
            var pvpResultPointRankTargetModels = new List<PvpBattleResultPointRankTargetModel>();
            
            var betweenRankModels = mstPvpRankModels
                .Skip(beforeRankModelIndex + 1)
                .Take(updatedRankModelIndex - beforeRankModelIndex)
                .ToList();
            
            var beforePoint = beforePvpPoint;
            var beforeRequiredLowerPoint = beforeRankModel.RequiredLowerPoint;
            foreach (var rankModel in betweenRankModels)
            {
                var targetPoint = PvpPoint.Min(afterPvpPoint, rankModel.RequiredLowerPoint);
                if (targetPoint.IsEmpty())
                {
                    targetPoint = afterPvpPoint;
                }
                
                var targetModel = new PvpBattleResultPointRankTargetModel(
                    beforePoint,
                    targetPoint,
                    rankModel.RequiredLowerPoint,
                    rankModel.RankClassType,
                    rankModel.RankLevel,
                    beforeRequiredLowerPoint);
                
                pvpResultPointRankTargetModels.Add(targetModel);
                
                beforePoint = targetPoint;
                beforeRequiredLowerPoint = rankModel.RequiredLowerPoint;
            }
            
            return pvpResultPointRankTargetModels;
        }
        
        PvpBattleResultPointModel CreatePvpRankDownResultPointModel(
            PvpPoint beforePvpPoint,
            PvpPoint afterPvpPoint,
            PvpEndResultBonusPointModel resultBonusPointModel)
        {
            var mstPvpRankModels = MstPvpDataRepository.GetMstPvpRanks()
                .OrderBy(model => model.RequiredLowerPoint)
                .ToList();
            
            var beforeRankModel = MstPvpRankModel.Empty;
            var beforeRankModelIndex = -1;
            for (var i = 0; i < mstPvpRankModels.Count; i++)
            {
                var model = mstPvpRankModels[i];
                if (model.RequiredLowerPoint <= beforePvpPoint)
                {
                    // 加算前のランクモデルを取得
                    beforeRankModel = model;
                    beforeRankModelIndex = i;
                }
                else
                {
                    // 加算前のランクの次のランクの情報までを取得
                    break;
                }
                
            }
            
            var updatedRankModelIndex = -1;
            for (var i = 0; i < mstPvpRankModels.Count; i++)
            {
                var model = mstPvpRankModels[i];
                if (model.RequiredLowerPoint <= afterPvpPoint)
                {
                    // 加算後のランクがどのランクに該当するかを取得
                    updatedRankModelIndex = i;
                }
                else
                {
                    break;
                }
            }
            
            var pvpResultPointRankTargetModels = CreatePointDownedRankTargetModels(
                beforePvpPoint,
                afterPvpPoint,
                beforeRankModelIndex,
                updatedRankModelIndex,
                mstPvpRankModels);

            return new PvpBattleResultPointModel(
                beforeRankModel.RankClassType,
                beforeRankModel.RankLevel,
                pvpResultPointRankTargetModels,
                resultBonusPointModel.ResultPoint,
                resultBonusPointModel.OpponentBonusPoint,
                resultBonusPointModel.TimeBonusPoint,
                afterPvpPoint
            );
        }
        
        IReadOnlyList<PvpBattleResultPointRankTargetModel> CreatePointDownedRankTargetModels(
            PvpPoint beforePvpPoint,
            PvpPoint afterPvpPoint,
            int beforeRankModelIndex,
            int updatedRankModelIndex,
            IReadOnlyList<MstPvpRankModel> mstPvpRankModels)
        {
            var pvpResultPointRankTargetModels = new List<PvpBattleResultPointRankTargetModel>();
            
            var beforePoint = beforePvpPoint;
            for (var i = beforeRankModelIndex; i >= updatedRankModelIndex; i--)
            {
                var prevRankModel = mstPvpRankModels.ElementAtOrDefault(i - 1, MstPvpRankModel.Empty);
                var currentRankModel = mstPvpRankModels.ElementAtOrDefault(i, MstPvpRankModel.Empty);
                var nextRankModel = mstPvpRankModels.ElementAtOrDefault(i + 1, MstPvpRankModel.Empty);
                
                var targetPoint = PvpPoint.Max(afterPvpPoint, currentRankModel.RequiredLowerPoint);
                
                var targetModel = new PvpBattleResultPointRankTargetModel(
                    beforePoint,
                    targetPoint,
                    currentRankModel.RequiredLowerPoint,
                    prevRankModel.RankClassType,
                    prevRankModel.RankLevel,
                    nextRankModel.RequiredLowerPoint);
                
                pvpResultPointRankTargetModels.Add(targetModel);
                
                beforePoint = targetPoint;
            }

            return pvpResultPointRankTargetModels;
        }
    }
}