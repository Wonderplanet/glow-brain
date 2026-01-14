using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models.PersistentStateKomaEffectModel;
using GLOW.Scenes.InGame.Domain.Models.StateEffectConditionModels;
using GLOW.Scenes.InGame.Domain.PresentationInterfaces;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle.UpdateProcess
{
    public class KomaEffectProcess : IKomaEffectProcess
    {
        [Inject] ICoordinateConverter CoordinateConverter { get; }
        [Inject] IStateEffectModelFactory StateEffectModelFactory { get; }
        [Inject] IBattlePresenter BattlePresenter { get; }
        [Inject] IStateEffectChecker StateEffectChecker { get; }

        public KomaEffectProcessResult UpdateKomaEffects(
            IReadOnlyList<CharacterUnitModel> characterUnits,
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            MstPageModel mstPageModel,
            TickCount tickCount,
            bool isBossAppearancePause)
        {
            var blockedUnits = new List<FieldObjectId>();
            // KomaEffectModelの更新
            var updatedKomaDictionary = komaDictionary;

            if (!isBossAppearancePause)
            {
                updatedKomaDictionary = UpdatedKomaEffect(komaDictionary, characterUnits, tickCount);
            }

            // コマを跨いだキャラに対する更新
            var updatedCharacterUnits = new List<CharacterUnitModel>();

            foreach (var characterUnit in characterUnits)
            {
                var updatedCharacterUnit = UpdateCharacterUnits(characterUnit);
                updatedCharacterUnits.Add(updatedCharacterUnit.unit);
                blockedUnits.AddRange(updatedCharacterUnit.blockedUnits);
            }

            // 状態変化以外のキャラへの効果
            IReadOnlyList<CharacterUnitModel> affectedCharacterUnits = updatedCharacterUnits.AsReadOnly();

            foreach (var koma in komaDictionary.Values)
            {
                foreach (var komaEffect in koma.KomaEffects)
                {
                    (var updatedUnitList, var blockedCharacterUnitIdList) =
                        komaEffect.AffectCharacterUnits(
                            affectedCharacterUnits,
                            mstPageModel,
                            CoordinateConverter,
                            StateEffectChecker);

                    affectedCharacterUnits = updatedUnitList;

                    foreach (var id in blockedCharacterUnitIdList)
                    {
                        blockedUnits.Add(id);
                    }
                }
            }

            return new KomaEffectProcessResult(updatedKomaDictionary, affectedCharacterUnits, blockedUnits);
        }

        static Dictionary<KomaId, KomaModel> UpdatedKomaEffect(
            IReadOnlyDictionary<KomaId, KomaModel> komaDictionary,
            IReadOnlyList<CharacterUnitModel> units,
            TickCount tickCount)
        {
            var updatedKomaDictionary = new Dictionary<KomaId, KomaModel>();

            var context = new KomaEffectUpdateContext(units, tickCount);

            foreach ((var komaId, var komaModel) in komaDictionary)
            {
                var updatedKoma = komaModel with
                {
                    KomaEffects = komaModel.KomaEffects.Select(effect => effect.GetUpdatedModel(context)).ToList()
                };

                updatedKomaDictionary.Add(komaId, updatedKoma);
            }

            return updatedKomaDictionary;
        }

        (CharacterUnitModel unit, List<FieldObjectId> blockedUnits) UpdateCharacterUnits(CharacterUnitModel characterUnitModel)
        {
            var blockedUnits = new List<FieldObjectId>();
            // コマ効果を適用させたくないキャラは何もしない
            if (!characterUnitModel.ShouldAttachKomaEffect) return (characterUnitModel, blockedUnits);

            // いるコマが変わっておらず、常時付与できる状態変化がない場合は何もしない
            var isLocatedKomaChanged = characterUnitModel.LocatedKoma.Id != characterUnitModel.PrevLocatedKoma.Id;
            if (!isLocatedKomaChanged && !characterUnitModel.LocatedKoma.ExistsKomaEffectsAlwaysActive())
            {
                return (characterUnitModel, blockedUnits);
            }

            // 前回いたコマによる状態変化を取り除く
            var updatedEffectList = characterUnitModel.StateEffects;
            if (isLocatedKomaChanged)
            {
                updatedEffectList = RemoveCharacterUnitStateEffects(
                    updatedEffectList,
                    characterUnitModel.PrevLocatedKoma);
            }

            // いまいるコマのキャラに有効な状態変化を付与するコマ効果を取得
            var result = GetEffectiveKomaEffectsForStateEffect(
                isLocatedKomaChanged,
                characterUnitModel,
                characterUnitModel.LocatedKoma,
                updatedEffectList,
                out var updatedEffectListByKomaEffectBlock);

            updatedEffectList = updatedEffectListByKomaEffectBlock;

            // ブロックが発生した場合
            if (result.blockedFlag)
            {
                blockedUnits.Add(characterUnitModel.Id);
            }

            // いまいるコマの状態変化効果を取得
            var additionStateEffectList = GetStateEffects(
                characterUnitModel,
                result.effectiveKomaEffects,
                characterUnitModel.LocatedKoma.StateEffectSourceId,
                updatedEffectList,
                out var updatedEffectListByKomaEffectBoost);

            updatedEffectList = updatedEffectListByKomaEffectBoost;

            var newStateEffectList = updatedEffectList.Concat(additionStateEffectList).ToList();
            return (characterUnitModel with { StateEffects = newStateEffectList }, blockedUnits);
        }

        (List<IKomaEffectModel> effectiveKomaEffects, bool blockedFlag)
            GetEffectiveKomaEffectsForStateEffect(
            bool isLocatedKomaChanged,
            CharacterUnitModel characterUnitModel,
            KomaModel komaModel,
            IReadOnlyList<IStateEffectModel> currentStateEffects,
            out IReadOnlyList<IStateEffectModel> updatedEffects)
        {
            var effectiveKomaEffects = new List<IKomaEffectModel>();
            updatedEffects = currentStateEffects;
            var blockedFlag = false;

            foreach (var komaEffect in komaModel.KomaEffects)
            {
                if (!komaEffect.ExistsStateEffect()) continue;
                if (!komaEffect.IsTarget(characterUnitModel)) continue;
                if (!komaEffect.IsAlwaysActive() && !isLocatedKomaChanged) continue;

                // 状態変化によるコマ効果無効
                var stateEffects = komaEffect.GetStateEffectsThatBlockableThis();
                bool isBlocked = false;

                foreach (var stateEffect in stateEffects)
                {
                    var stateEffectCheckResult = StateEffectChecker.CheckAndReduceCount(
                        stateEffect,
                        updatedEffects,
                        StateEffectEmptyConditionContext.Instance,
                        komaModel.StateEffectSourceId);
                    updatedEffects = stateEffectCheckResult.UpdatedStateEffects;
                    if (stateEffectCheckResult.IsEffectActivated)
                    {
                        isBlocked = true;
                    }
                }

                if (isBlocked)
                {
                    blockedFlag = true;
                    continue;
                }

                effectiveKomaEffects.Add(komaEffect);
            }

            return (effectiveKomaEffects, blockedFlag);
        }

        /// <summary>
        /// コマ効果によって付与された状態変化を取り除く
        /// </summary>
        IReadOnlyList<IStateEffectModel> RemoveCharacterUnitStateEffects(
            IReadOnlyList<IStateEffectModel> stateEffectModels,
            KomaModel prevLocatedKomaModel)
        {
            if (!prevLocatedKomaModel.ExistsKomaEffects()) return stateEffectModels;

            var updatedStateEffects =  stateEffectModels
                .Where(effect => effect.SourceId != prevLocatedKomaModel.StateEffectSourceId)
                .ToList();

            // コマから出ても効果が持続するStateEffectを毎フレーム付与するコマから出た場合（毒、やけどなど）
            var persistentStateKomaEffects = prevLocatedKomaModel.KomaEffects.OfType<PersistentStateKomaEffectModel>();
            foreach (var persistentKoma in persistentStateKomaEffects)
            {
                // 前回いたコマから付与されたStateEffectを取得
                var persistentStateEffects = stateEffectModels
                        .Where(effect => effect.Type == persistentKoma.GetStateEffectType()
                        && effect.SourceId == prevLocatedKomaModel.StateEffectSourceId).ToList();
                // StateEffectが存在しなければcontinue
                if (!persistentStateEffects.Any()) continue;

                // 効果時間を更新して再付与する
                var stateEffect = persistentStateEffects.First();
                var duration = persistentKoma.RemainingDuration;
                var updatedStateEffect = persistentKoma.UpdateDuration(stateEffect, duration);
                updatedStateEffects.Add(updatedStateEffect);
            }

            return updatedStateEffects;
        }

        /// <summary>
        /// コマ効果によって付与される状態変化を取得する
        /// </summary>
        List<IStateEffectModel> GetStateEffects(
            CharacterUnitModel characterUnitModel,
            IReadOnlyList<IKomaEffectModel> komaEffectModels,
            StateEffectSourceId stateEffectSourceId,
            IReadOnlyList<IStateEffectModel> currentStateEffects,
            out IReadOnlyList<IStateEffectModel> updatedEffects)
        {
            var additionStateEffects = new List<IStateEffectModel>();
            updatedEffects = currentStateEffects;

            if (characterUnitModel.Action.IsNonAttackStateEffectInvalidation)
            {
                return additionStateEffects;
            }

            // コマの状態変化効果を付与する
            foreach (var komaEffect in komaEffectModels)
            {
                // 状態変化によるコマ効果ブースト
                var stateEffectsThatBoostKomaEffect = komaEffect.GetStateEffectsThatBoostThis();
                var boostParameters = new List<StateEffectParameter>();

                foreach (var stateEffect in stateEffectsThatBoostKomaEffect)
                {
                    var stateEffectCheckResult = StateEffectChecker.CheckAndReduceCount(stateEffect, updatedEffects);
                    updatedEffects = stateEffectCheckResult.UpdatedStateEffects;
                    if (stateEffectCheckResult.IsEffectActivated)
                    {
                        boostParameters.AddRange(stateEffectCheckResult.Parameters);
                    }
                }

                // コマ効果による状態変化
                var stateEffectByKomaEffect = komaEffect.GetStateEffect(characterUnitModel.BattleSide, boostParameters);
                if (stateEffectByKomaEffect.IsEmpty()) continue;

                if (StateEffectChecker.ShouldAttachHasNotMultiState(stateEffectByKomaEffect, updatedEffects))
                {
                   var effectModel = StateEffectModelFactory.Create(
                       stateEffectSourceId, 
                       stateEffectByKomaEffect, 
                       komaEffect.IsStateEffectVisible());
                   
                    additionStateEffects.Add(effectModel);
                }
            }

            return additionStateEffects;
        }
    }
}
