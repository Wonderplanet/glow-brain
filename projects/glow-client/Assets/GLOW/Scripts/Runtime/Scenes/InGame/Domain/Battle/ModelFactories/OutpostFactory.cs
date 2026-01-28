using System.Collections.Generic;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Scenes.InGame.Domain.Battle.Calculator;
using GLOW.Scenes.InGame.Domain.Constants;
using GLOW.Scenes.InGame.Domain.Models;
using GLOW.Scenes.InGame.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle
{
    public class OutpostFactory : IOutpostFactory
    {
        [Inject] IFieldObjectIdProvider FieldObjectIdProvider { get; }
        [Inject] IStateEffectSourceIdProvider StateEffectSourceIdProvider { get; }
        [Inject] IOutpostMaxHpCalculator OutpostMaxHpCalculator { get; }

        public OutpostModel GenerateOutpost(
            MstArtworkModel artworkModel,
            OutpostAssetKey outpostAssetKey,
            QuestType questType,
            IReadOnlyList<MstInGameSpecialRuleModel> specialRules,
            OutpostEnhancementModel outpostEnhancement,
            HP artworkBonusHp,
            InGameContinueSelectingFlag isContinueSelecting)
        {
            var id = FieldObjectIdProvider.GenerateNewId();
            var stateEffectSourceId = StateEffectSourceIdProvider.GenerateNewId();
            var pos = new OutpostCoordV2(0f, 0f);

            var maxHpResult = OutpostMaxHpCalculator.Calculate(
                outpostEnhancement,
                artworkBonusHp,
                specialRules);

            var maxHp = maxHpResult.Hp;
            var hp = isContinueSelecting ? HP.Zero : maxHp;

            // todo:降臨では味方ゲートが破壊されないためここでtrueを入れる
            var unBroken = questType switch
            {
                _ => OutpostDamageInvalidationFlag.False
            };

            return new OutpostModel(
                id,
                stateEffectSourceId,
                BattleSide.Player,
                ArtworkAssetPath.CreateSmall(artworkModel.AssetKey),
                outpostAssetKey.IsEmpty() ? OutpostAssetKey.PlayerDefault : outpostAssetKey,
                maxHp,
                hp,
                unBroken,
                pos,
                maxHpResult.IsOverride);
        }

        public OutpostModel GenerateOutpost(MstEnemyOutpostModel outpost)
        {
            var id = FieldObjectIdProvider.GenerateNewId();
            var stateEffectSourceId = StateEffectSourceIdProvider.GenerateNewId();
            var pos = new OutpostCoordV2(0f, 0f);

            var artworkAssetKey = outpost.OutpostAssetKey.IsEmpty() && !outpost.ArtworkAssetKey.IsEmpty()
                ? ArtworkAssetPath.CreateSmall(outpost.ArtworkAssetKey)
                : ArtworkAssetPath.Empty;

            return new OutpostModel(
                id,
                stateEffectSourceId,
                BattleSide.Enemy,
                artworkAssetKey,
                outpost.OutpostAssetKey.IsEmpty() ? OutpostAssetKey.EnemyDefault : outpost.OutpostAssetKey,
                outpost.Hp,
                outpost.Hp,
                outpost.IsDamageInvalidation,
                pos,
                OutpostHpSpecialRuleFlag.False);
        }

        /// <summary> Pvpでの対戦相手用のゲート計算 </summary>
        public OutpostModel GenerateOpponentOutpost(
            OutpostEnhancementModel outpostEnhancement,
            HP artworkBonusHp,
            MstEnemyOutpostModel outpost)
        {
            var id = FieldObjectIdProvider.GenerateNewId();
            var stateEffectSourceId = StateEffectSourceIdProvider.GenerateNewId();
            var pos = new OutpostCoordV2(0f, 0f);

            var artworkAssetKey = outpost.OutpostAssetKey.IsEmpty() && !outpost.ArtworkAssetKey.IsEmpty()
                ? ArtworkAssetPath.CreateSmall(outpost.ArtworkAssetKey)
                : ArtworkAssetPath.Empty;

            var maxHpResult = OutpostMaxHpCalculator.Calculate(
                outpostEnhancement,
                artworkBonusHp,
                new List<MstInGameSpecialRuleModel>());

            var maxHp = maxHpResult.Hp;
            var hp = maxHp;

            return new OutpostModel(
                id,
                stateEffectSourceId,
                BattleSide.Enemy,
                artworkAssetKey,
                outpost.OutpostAssetKey.IsEmpty() ? OutpostAssetKey.PvpOpponentDefault : outpost.OutpostAssetKey,
                maxHp,
                hp,
                outpost.IsDamageInvalidation,
                pos,
                OutpostHpSpecialRuleFlag.False);
        }
    }
}
