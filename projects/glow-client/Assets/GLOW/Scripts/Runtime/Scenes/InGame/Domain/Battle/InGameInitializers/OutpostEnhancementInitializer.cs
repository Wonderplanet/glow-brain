using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Scenes.InGame.Domain.ModelFactories;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Scenes.InGame.Domain.Battle.InGameInitializers
{
    public class OutpostEnhancementInitializer : IOutpostEnhancementInitializer
    {
        [Inject] IOutpostEnhancementModelFactory OutpostEnhancementModelFactory { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IPvpSelectedOpponentStatusCacheRepository PvpSelectedOpponentStatusCacheRepository { get; }

        public OutpostEnhancementInitializerResult Initialize(InGameType inGameType)
        {
            OutpostEnhancementModel outpostEnhancement;
            OutpostEnhancementModel pvpOpponentOutpostEnhancement;

            var isTutorial = GameRepository.GetGameFetchOther().TutorialStatus.IsIntroduction();
            if (isTutorial)
            {
                outpostEnhancement = OutpostEnhancementModelFactory.CreateForTutorialBattle();
                pvpOpponentOutpostEnhancement = OutpostEnhancementModel.Empty;
            }
            else
            {
                outpostEnhancement = OutpostEnhancementModelFactory.Create();

                // PVPの場合は対戦相手の強化情報を取得した上で、
                // プレイヤーと対戦相手双方に撃破時のBP加算が無いように加算強化値を除外する
                pvpOpponentOutpostEnhancement = OutpostEnhancementModel.Empty;
                if (inGameType == InGameType.Pvp)
                {
                    var opponentStatus = PvpSelectedOpponentStatusCacheRepository.GetOpponentStatus();
                    pvpOpponentOutpostEnhancement = opponentStatus.IsEmpty()
                        ? OutpostEnhancementModel.Empty
                        : OutpostEnhancementModelFactory.CreateOpponent(opponentStatus.UsrOutpostEnhancements);

                    // プレイヤー側
                    outpostEnhancement = outpostEnhancement with
                    {
                        EnhancementElements = outpostEnhancement.EnhancementElements
                            .Where(x => x.Type != OutpostEnhancementType.LeaderPointUp)
                            .ToList()
                    };

                    // 対戦相手側
                    pvpOpponentOutpostEnhancement = pvpOpponentOutpostEnhancement with
                    {
                        EnhancementElements = pvpOpponentOutpostEnhancement.EnhancementElements
                            .Where(x => x.Type != OutpostEnhancementType.LeaderPointUp)
                            .ToList()
                    };
                }
            }

            return new OutpostEnhancementInitializerResult(
                outpostEnhancement,
                pvpOpponentOutpostEnhancement);
        }
    }
}

