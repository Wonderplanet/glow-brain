#if GLOW_INGAME_DEBUG
using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Battle;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Debugs.Home.Domain.Constants;
using GLOW.Debugs.InGame.Domain.Definitions;
using GLOW.Debugs.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Debugs.InGame.Domain.UseCases
{
    /// <summary>
    /// デバッグ用：敵キャラを即時召喚するUseCase
    /// </summary>
    public sealed class DebugSummonEnemyUnitUseCase
    {
        [Inject] IInGameScene InGameScene { get; }
        [Inject] IMstEnemyCharacterDataRepository MstEnemyCharacterDataRepository { get; }
        [Inject] IUnitGenerationModelFactory UnitGenerationModelFactory { get; }
        [Inject] InitialEnemyCharacterCoefFactory InitialCharacterUnitCoefFactory { get; }
        [Inject] IDeckSpecialUnitSummonEvaluator DeckSpecialUnitSummonEvaluator { get; }
        [Inject] ICoordinateConverter CoordinateConverter { get; }
        [Inject] IMstAutoPlayerSequenceRepository MstAutoPlayerSequenceRepository { get; }
        [Inject] IInGameDebugSettingRepository DebugSettingRepository { get; }

        public void SummonEnemy(DebugSummonTargetId debugSummonTargetId)
        {
            if (InGameScene.Type == InGameType.Pvp)
            {
                SummonPvpOpponentUnit(debugSummonTargetId);
            }
            else
            {
                SummonAutoPlayerSequenceEnemy(debugSummonTargetId);
            }
        }

        void SummonAutoPlayerSequenceEnemy(DebugSummonTargetId debugSummonTargetId)
        {
            var mstAutoPlayerSequenceSetId = InGameScene.MstInGame.MstAutoPlayerSequenceSetId;

            if (DebugSettingRepository.Get().IsOverrideSummons)
            {
                mstAutoPlayerSequenceSetId = DebugMstUnitTemporaryParameterDefinitions.DebugSequenceId;
            }

            // すべてのSequenceElementStateModelsから指定IdのMstAutoPlayerSequenceElementModelを検索
            var elementModel = FindElementModelFromAllSequenceGroups(mstAutoPlayerSequenceSetId, debugSummonTargetId);
            if (elementModel.IsEmpty()) return;
            if (elementModel.Action.Type != AutoPlayerSequenceActionType.SummonEnemy) return;

            // UnitGenerationModelFactoryで生成
            var unitGenerationModel = UnitGenerationModelFactory.Create(
                elementModel,
                BattleSide.Enemy,
                InGameScene.MstInGame,
                InitialCharacterUnitCoefFactory
            );

            var enemyStageParameterId = elementModel.Action.Value.ToMasterDataId();
            var enemyStageParameter = MstEnemyCharacterDataRepository.GetEnemyStageParameter(enemyStageParameterId);

            if (enemyStageParameter.IsBoss)
            {
                // ボスの場合はBossSummonQueueに追加
                var bossQueue = InGameScene.BossSummonQueue;
                var newBossElement = new BossSummonQueueElement(enemyStageParameterId, unitGenerationModel);
                var newBossQueue = bossQueue with { SummonQueue = bossQueue.SummonQueue.Append(newBossElement).ToList() };
                InGameScene.BossSummonQueue = newBossQueue;
            }
            else
            {
                // 通常敵の場合は従来通りUnitSummonQueueに追加
                var queue = InGameScene.UnitSummonQueue;
                var newElement = new UnitSummonQueueElement(enemyStageParameterId, unitGenerationModel);
                var newQueue = queue with { SummonQueue = queue.SummonQueue.Append(newElement).ToList() };
                InGameScene.UnitSummonQueue = newQueue;
            }
        }

        void SummonPvpOpponentUnit(DebugSummonTargetId debugSummonTargetId)
        {
            var pvpOpponentDeckUnits = InGameScene.PvpOpponentDeckUnits;

            // DebugSummonTargetIdの値から該当するユニットを特定
            var deckUnitIndex = debugSummonTargetId.ToDeckUnitIndex();
            if (deckUnitIndex.Value < 0 || deckUnitIndex.Value >= pvpOpponentDeckUnits.Count) return;

            var targetUnit = pvpOpponentDeckUnits[deckUnitIndex.Value];

            // スペシャルユニットか通常ユニットかで処理を分岐
            if (targetUnit.RoleType == CharacterUnitRoleType.Special)
            {
                // DeckAutoPlayerProcessorと同様の処理でスペシャルユニットの召喚位置を取得
                var summonPosition = GetSpecialUnitSummonPosition(
                    InGameScene.CharacterUnits,
                    InGameScene.SpecialUnitSummonInfoModel,
                    InGameScene.MstPage,
                    InGameScene.KomaDictionary);

                // 召喚すべき位置がない場合は召喚しない
                if (!summonPosition.IsEmpty())
                {
                    var specialSummonQueueElement = new SpecialUnitSummonQueueElement(
                        BattleSide.Enemy,
                        targetUnit.CharacterId,
                        summonPosition
                    );
                    var specialUnitSummonQueue = InGameScene.SpecialUnitSummonQueue;

                    var updatedSpecialQueue = specialUnitSummonQueue with
                    {
                        SummonQueue = specialUnitSummonQueue.SummonQueue.ToList().ChainAdd(specialSummonQueueElement)
                    };

                    InGameScene.SpecialUnitSummonQueue = updatedSpecialQueue;
                }
            }
            else
            {
                // 通常ユニットの召喚
                var summonQueueElement = new DeckUnitSummonQueueElement(targetUnit.CharacterId, BattleSide.Enemy);
                var deckUnitSummonQueue = InGameScene.DeckUnitSummonQueue;

                var updatedQueue = deckUnitSummonQueue with
                {
                    SummonQueue = deckUnitSummonQueue.SummonQueue.ToList().ChainAdd(summonQueueElement)
                };

                InGameScene.DeckUnitSummonQueue = updatedQueue;
            }
        }

        PageCoordV2 GetSpecialUnitSummonPosition(
            IReadOnlyList<CharacterUnitModel> units,
            SpecialUnitSummonInfoModel specialUnitSummonInfo,
            MstPageModel mstPage,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary)
        {
            // 敵キャラを前線から順に並べる（Enemy側の処理）
            var sortedEnemyUnits = units
                .Where(unit => unit.BattleSide == BattleSide.Enemy)
                .OrderByDescending(unit => unit.Pos.X);

            // 前線に近い方からスペシャルキャラを召喚できるコマか判定
            var prevCheckedKomaId = KomaId.Empty;

            foreach (var unit in sortedEnemyUnits)
            {
                if (unit.LocatedKoma.Id == prevCheckedKomaId) continue;
                prevCheckedKomaId = unit.LocatedKoma.Id;

                var komaNo = mstPage.GetKomaNo(unit.LocatedKoma.Id);

                if (!DeckSpecialUnitSummonEvaluator.IsSummonableKoma(
                    komaNo,
                    specialUnitSummonInfo,
                    mstPage,
                    komaDictionary,
                    BattleSide.Enemy))  // Enemy側で求める
                {
                    continue;
                }

                var fieldCoord = CoordinateConverter.OutpostToFieldCoord(unit.BattleSide, unit.Pos);
                var pageCoord = CoordinateConverter.FieldToPageCoord(fieldCoord);

                return pageCoord;
            }

            return PageCoordV2.Empty;
        }

        MstAutoPlayerSequenceElementModel FindElementModelFromAllSequenceGroups(
            AutoPlayerSequenceSetId mstAutoPlayerSequenceSetId,
            DebugSummonTargetId debugSummonTargetId)
        {
            // リポジトリからMstAutoPlayerSequenceModelを取得
            var enemyAutoPlayerSequenceModel = MstAutoPlayerSequenceRepository.GetMstAutoPlayerSequence(mstAutoPlayerSequenceSetId);

            if (enemyAutoPlayerSequenceModel.IsEmpty())
            {
                return MstAutoPlayerSequenceElementModel.Empty;
            }

            // すべてのElementsから検索（InGameDebugInitializerと同じロジック）
            return enemyAutoPlayerSequenceModel.Elements
                .Where(element => element.SequenceSetId == mstAutoPlayerSequenceSetId)
                .Where(element => element.Action.Type == AutoPlayerSequenceActionType.SummonEnemy)
                .FirstOrDefault(
                    element => element.SequenceElementId.Value == debugSummonTargetId.Value,
                    MstAutoPlayerSequenceElementModel.Empty);
        }
    }
}
#endif

