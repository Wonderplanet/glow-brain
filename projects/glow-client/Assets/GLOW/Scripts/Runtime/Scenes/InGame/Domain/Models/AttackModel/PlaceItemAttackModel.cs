using System;
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Battle;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models.AttackResultModel;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using WonderPlanet.UnityStandard.Extension;

namespace GLOW.Scenes.InGame.Domain.Models.AttackModel
{
    public record PlaceItemAttackModel(
        AttackId Id,
        FieldObjectId AttackerId,
        BattleSide PlacedItemBattleSide,
        AttackElement AttackElement,
        TickCount RemainingDelay,
        bool IsEnd) : IAttackModel
    {
        AttackViewId IAttackModel.ViewId => AttackElement.AttackViewId;
        public StateEffectSourceId AttackerStateEffectSourceId => StateEffectSourceId.Empty;
        public CharacterUnitRoleType AttackerRoleType => CharacterUnitRoleType.None;
        public CharacterColor AttackerColor => CharacterColor.None;
        public IReadOnlyList<CharacterColor> KillerColors => Array.Empty<CharacterColor>();
        public KillerPercentage KillerPercentage => KillerPercentage.Empty;
        public AttackPower BasePower => AttackPower.Empty;
        public HealPower HealPower => HealPower.Empty;
        public CharacterColorAdvantageAttackBonus ColorAdvantageAttackBonus => CharacterColorAdvantageAttackBonus.Empty;
        public IReadOnlyList<PercentageM> BuffPercentages => Array.Empty<PercentageM>();
        public IReadOnlyList<PercentageM> DebuffPercentages => Array.Empty<PercentageM>();
        
        const float OutpostMarginMultiplier = 2.0f;
        
        public bool IsEmpty()
        {
            return false;
        }

        public (IAttackModel, IReadOnlyList<IAttackResultModel>) UpdateAttackModel(AttackModelContext context)
        {
            PlaceItemAttackModel updatedAttack;
            IReadOnlyList<IAttackResultModel> attackResults = Array.Empty<IAttackResultModel>();

            if (!RemainingDelay.IsEmpty())
            {
                var remainingDelay = RemainingDelay - context.TickCount;

                if (remainingDelay.IsZero())
                {
                    updatedAttack = this with { RemainingDelay = remainingDelay, IsEnd = true };
                    attackResults = GetAttackResults(context);
                }
                else
                {
                    updatedAttack = this with { RemainingDelay = remainingDelay, IsEnd = false };
                }
            }
            else
            {
                updatedAttack = this with { IsEnd = true };
                attackResults = GetAttackResults(context);
            }

            return (updatedAttack, attackResults);
        }
        
        IReadOnlyList<IAttackResultModel> GetAttackResults(AttackModelContext context)
        {
            var attackResultModelFactory = context.AttackResultModelFactory;

            var attackResults = new List<IAttackResultModel>();
            
            var alreadyPlacedItemKomaIdSet = context.AlreadyPlacedItemKomaIdSet;
            
            var komaIdAndPosList = CalculateItemPlacedPos(
                context.KomaDictionary,
                context.MstPage,
                alreadyPlacedItemKomaIdSet, 
                AttackElement.MaxTargetCount.Value,
                context.RandomProvider);

            for (var i = 0; i < AttackElement.MaxTargetCount.Value; i++)
            {
                var komaId = komaIdAndPosList[i].KomaId;
                var pos = komaIdAndPosList[i].Pos;
                // アイテム配置用のデータを作成。MaxTargetCountの分だけ作成する
                attackResults.Add(attackResultModelFactory.CreatePlacedItem(
                    this, 
                    PlacedItemBattleSide, 
                    komaId,
                    pos,
                    AttackElement));
            }

            return attackResults;
        }
        
        IReadOnlyList<(KomaId KomaId, FieldCoordV2 Pos)> CalculateItemPlacedPos(
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            MstPageModel page,
            HashSet<KomaId> alreadyPlacedItemKomaIdSet,
            int newPlacedItemCount,
            IRandomProvider randomProvider)
        {
            var komaIdAndPos = new List<(KomaId, FieldCoordV2)>();
            if(newPlacedItemCount <= 0) return komaIdAndPos;
            
            // 敵側の拠点を考慮した配置可能範囲
            var maxPlaceablePosX = page.TotalWidth - InGameConstants.DefaultSummonPos.X * OutpostMarginMultiplier;
            
            // 味方側の拠点を考慮した配置可能範囲
            var minPlaceablePosX = InGameConstants.DefaultSummonPos.X * OutpostMarginMultiplier;
            
            var placableKomaIds = komaDictionary
                .Where(koma => !alreadyPlacedItemKomaIdSet.Contains(koma.Key))
                .Where(koma =>
                {
                    var komaRange = page.GetKomaRange(koma.Key);
                    
                    // コマの左端より最小配置範囲が右、コマの右端より最大配置範囲が左の場合は配置不可
                    return komaRange.Max > minPlaceablePosX && komaRange.Min < maxPlaceablePosX;
                })
                .AsShuffle()
                .Select(koma => koma.Key)
                .ToList();
            
            // 配置可能なコマが新規配置数以上ある場合は、数分取り出して配置(shuffle済み)
            if (placableKomaIds.Count >= newPlacedItemCount)
            {
                for (var i = 0; i < newPlacedItemCount; i++)
                {
                    var komaId = placableKomaIds[i];
                    var komaRange = page.GetKomaRange(komaId);
                    komaIdAndPos.Add((komaId, AdjustItemPlacedPosConsiderOutpost(
                        komaRange,
                        randomProvider,
                        true,
                        maxPlaceablePosX,
                        minPlaceablePosX)));
                }
            }
            else
            {
                // 配置可能なコマを全て配置し、残りはランダムに配置
                var komaIdList = placableKomaIds
                    .Select(id => (id, AdjustItemPlacedPosConsiderOutpost(
                        page.GetKomaRange(id),
                        randomProvider,
                        true,
                        maxPlaceablePosX,
                        minPlaceablePosX)))
                    .ToList();
                komaIdAndPos.AddRange(komaIdList);
                
                var randomPlaceCount = newPlacedItemCount - placableKomaIds.Count;
                for (var i = 0; i < randomPlaceCount; i++)
                {
                    var komaId = komaDictionary.ElementAt(randomProvider.Range(0, komaDictionary.Count)).Key;
                    var komaRange = page.GetKomaRange(komaId);
                    komaIdAndPos.Add((
                        komaId, 
                        AdjustItemPlacedPosConsiderOutpost(
                            komaRange,
                            randomProvider,
                            false,
                            maxPlaceablePosX,
                            minPlaceablePosX)));
                }
            }
            
            return komaIdAndPos;
        }

        FieldCoordV2 AdjustItemPlacedPosConsiderOutpost(
            CoordinateRange komaRange,
            IRandomProvider randomProvider,
            bool useCenter,
            float maxPlaceablePosX,
            float minPlaceablePosX)
        {
            var rangeMax = komaRange.Max;
            var rangeMin = komaRange.Min;

            // 配置可能範囲内に調整(ゲートに被らないように)
            if (maxPlaceablePosX <= komaRange.Max)
            {
                rangeMax = maxPlaceablePosX;
            }
            if (minPlaceablePosX >= komaRange.Min)
            {
                rangeMin = minPlaceablePosX;
            }
            
            // ゲートの位置を考慮に入れたコマ中心座標かランダム座標を取得
            var placedPos = new FieldCoordV2(
                useCenter ? (rangeMax + rangeMin) / 2.0f : randomProvider.Range(rangeMin, rangeMax),
                0.0f);
            return placedPos;
        }
    }
}