using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.InGame;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public class OutpostInitializer : IOutpostInitializer
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IOutpostFactory OutpostFactory { get; }
        [Inject] IMstArtworkDataRepository MstArtworkDataRepository { get; }
        [Inject] IMstEnemyOutpostDataRepository MstEnemyOutpostDataRepository { get; }

        public OutpostInitializerResult Initialize(
            InGameType inGameType,
            QuestType questType,
            OutpostAssetKey outpostAssetKey,
            MasterDataId mstEnemyOutpostId,
            IReadOnlyList<MstInGameSpecialRuleModel> mstInGameSpecialRuleModels,
            OutpostEnhancementModel outpostEnhancement,
            OutpostEnhancementModel pvpOpponentOutpostEnhancement,
            HP artworkBonusHp,
            HP pvpOpponentArtworkBonusHp,
            InGameContinueSelectingFlag isContinueSelecting)
        {
            var playerOutpost = InitializePlayerOutpost(
                questType,
                outpostAssetKey,
                mstInGameSpecialRuleModels,
                outpostEnhancement,
                artworkBonusHp,
                isContinueSelecting);

            // 敵側(Pvpであれば対戦相手側)のゲート情報を生成
            var enemyOutpost = InitializeEnemyOutpost(
                inGameType,
                mstEnemyOutpostId,
                pvpOpponentOutpostEnhancement,
                pvpOpponentArtworkBonusHp);

            return new OutpostInitializerResult(
                playerOutpost,
                enemyOutpost);
        }

        OutpostModel InitializePlayerOutpost(
            QuestType questType,
            OutpostAssetKey outpostAssetKey,
            IReadOnlyList<MstInGameSpecialRuleModel> mstInGameSpecialRuleModels,
            OutpostEnhancementModel outpostEnhancement,
            HP artworkBonusHp,
            InGameContinueSelectingFlag isContinueSelecting)
        {
            var outpost = GameRepository.GetGameFetchOther().UserOutpostModels.Find(outpost => outpost.IsUsed);
            var mstArtwork = outpost.MstArtworkId.IsEmpty()
                ? MstArtworkDataRepository.GetArtworks().OrderBy(artwork => artwork.SortOrder).First()
                : MstArtworkDataRepository.GetArtwork(outpost.MstArtworkId);

            return OutpostFactory.GenerateOutpost(
                mstArtwork,
                outpostAssetKey,
                questType,
                mstInGameSpecialRuleModels,
                outpostEnhancement,
                artworkBonusHp,
                isContinueSelecting);
        }

        OutpostModel InitializeEnemyOutpost(
            InGameType inGameType,
            MasterDataId mstEnemyOutpostId,
            OutpostEnhancementModel outpostEnhancement,
            HP artworkBonusHp)
        {
            OutpostModel enemyOutpost;
            var mstEnemyOutpostModel = MstEnemyOutpostDataRepository.GetEnemyOutpost(mstEnemyOutpostId);
            if (inGameType == InGameType.Pvp)
            {
                // Pvpの時はゲート情報の生成をプレイヤーに合わせる
                enemyOutpost = OutpostFactory.GenerateOpponentOutpost(
                    outpostEnhancement,
                    artworkBonusHp,
                    mstEnemyOutpostModel);
            }
            else
            {
                enemyOutpost = OutpostFactory.GenerateOutpost(mstEnemyOutpostModel);
            }

            return enemyOutpost;
        }
    }
}
