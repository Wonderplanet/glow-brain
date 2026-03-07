using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.StateEffectConditionModels;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class StateEffectModelFactory : IStateEffectModelFactory
    {
        [Inject] IInGameScene InGameScene { get; }
        [Inject] IMstConfigRepository MstConfigRepository { get; }

        readonly StateEffectIdProvider _stateEffectIdProvider = new();

        public IStateEffectModel Create(StateEffectSourceId sourceId, StateEffect stateEffect, bool needsDisplay)
        {
            if (stateEffect.IsEmpty())
            {
                return EmptyStateEffectModel.Instance;
            }

            var id = _stateEffectIdProvider.GenerateNewId();

            var condition = CreateStateEffectConditionModel(stateEffect);

            return stateEffect.Type switch
            {
                StateEffectType.SlipDamage => new SlipDamageStateEffectModel(
                    id,
                    sourceId,
                    stateEffect.Type,
                    stateEffect.EffectiveCount,
                    stateEffect.EffectiveProbability,
                    stateEffect.Duration,
                    InGameScene.InGameSetting.SlipDamageInterval,
                    InGameScene.InGameSetting.SlipDamageInterval,
                    stateEffect.Parameter,
                    condition,
                    needsDisplay
                ),

                StateEffectType.Poison => new PoisonDamageStateEffectModel(
                    id,
                    sourceId,
                    stateEffect.Type,
                    stateEffect.EffectiveCount,
                    stateEffect.EffectiveProbability,
                    stateEffect.Duration,
                    InGameScene.InGameSetting.PoisonDamageInterval,
                    InGameScene.InGameSetting.PoisonDamageInterval,
                    stateEffect.Parameter,
                    condition,
                    needsDisplay
                ),

                StateEffectType.Burn => new BurnDamageStateEffectModel(
                    id,
                    sourceId,
                    stateEffect.Type,
                    stateEffect.EffectiveCount,
                    stateEffect.EffectiveProbability,
                    stateEffect.Duration,
                    InGameScene.InGameSetting.BurnDamageInterval,
                    InGameScene.InGameSetting.BurnDamageInterval,
                    stateEffect.Parameter,
                    condition,
                    needsDisplay
                ),

                StateEffectType.Freeze => CreateFreezeStateEffectModel(id, sourceId, stateEffect, condition, needsDisplay),

                // UnitAction変更の絡まない必殺技・コマ両方無効化する効果（PoisonBlock、WeakeningBlockなど）
                var type when BlockStateEffectModel.IsBlockStateEffect(type) => new BlockStateEffectModel(
                    id,
                    sourceId,
                    type,
                    stateEffect.EffectiveCount,
                    stateEffect.EffectiveProbability,
                    stateEffect.Duration,
                    stateEffect.Parameter,
                    condition,
                    needsDisplay,
                    StateEffectSourceId.Empty
                ),

                StateEffectType.RegenerationByFixed or
                StateEffectType.RegenerationByMaxHpPercentage => new RegenerationStateEffectModel(
                    id,
                    sourceId,
                    stateEffect.Type,
                    stateEffect.EffectiveCount,
                    stateEffect.EffectiveProbability,
                    stateEffect.Duration,
                    InGameScene.InGameSetting.RegenerationInterval,
                    InGameScene.InGameSetting.RegenerationInterval,
                    stateEffect.Parameter,
                    condition,
                    GeneratedFirstAttackFlag.False,
                    needsDisplay),

                _ => new StateEffectModel(
                    id,
                    sourceId,
                    stateEffect.Type,
                    stateEffect.EffectiveCount,
                    stateEffect.EffectiveProbability,
                    stateEffect.Duration,
                    stateEffect.Parameter,
                    condition,
                    needsDisplay
                )
            };
        }

        IStateEffectConditionModel CreateStateEffectConditionModel(StateEffect stateEffect)
        {
            // NOTE: CheckAndReduceCountの呼び出し方次第ではここで値を設定しても呼び出されないことがあるので確認する
            // IStateEffectConditionContextを使った条件判定を行うようにする必要がある
            return stateEffect.Type switch
            {
                StateEffectType.StunBlock or
                StateEffectType.FreezeBlock => new StateEffectAttackerConditionModel(
                    stateEffect.ConditionValue1.ToCharacterUnitRoleTypes(),
                    stateEffect.ConditionValue2.ToCharacterColors()
                ),
                // 必殺技・コマ効果両方の付与を無効化する
                StateEffectType.PoisonBlock or
                StateEffectType.WeakeningBlock => new StateEffectAttackerOrEmptyConditionModel(
                    stateEffect.ConditionValue1.ToCharacterUnitRoleTypes(),
                    stateEffect.ConditionValue2.ToCharacterColors(),
                    ConditionMatchType.Or
                ),
                // 無敵付与の攻撃者対象条件はAND条件
                StateEffectType.Unbeatable => new StateEffectAttackerOrEmptyConditionModel(
                    stateEffect.ConditionValue1.ToCharacterUnitRoleTypes(),
                    stateEffect.ConditionValue2.ToCharacterColors(),
                    ConditionMatchType.And
                ),

                _ => StateEffectAlwaysConditionModel.Instance
            };
        }

        StateEffectModel CreateFreezeStateEffectModel(
            StateEffectId id,
            StateEffectSourceId sourceId,
            StateEffect stateEffect,
            IStateEffectConditionModel condition,
            bool needsDisplay)
        {
            var freezeDamageIncreasePercentage =
                MstConfigRepository.GetConfig(MstConfigKey.FreezeDamageIncreasePercentage);

            return new StateEffectModel(
                id,
                sourceId,
                stateEffect.Type,
                stateEffect.EffectiveCount,
                stateEffect.EffectiveProbability,
                stateEffect.Duration,
                new StateEffectParameter(freezeDamageIncreasePercentage.Value.ToInt()),
                condition,
                needsDisplay
            );
        }

        /// <summary>
        /// アイコン表示のみを行い、実際の効果を持たないStateEffectModelを作成
        /// 主に原画効果など、ステータスには既に反映済みでアイコン表示のみ必要な場合に使用
        /// </summary>
        IStateEffectModel IStateEffectModelFactory.CreateDisplayOnly(StateEffectSourceId sourceId, StateEffectType stateEffectType)
        {
            var id = _stateEffectIdProvider.GenerateNewId();
            return new DisplayOnlyStateEffectModel(id, sourceId, stateEffectType);
        }
    }
}
