using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.OutpostEnhance;
using GLOW.Core.Extensions;
using GLOW.Scenes.InGame.Domain.Repositories;
using GLOW.Scenes.OutpostEnhance.Domain.Models;
using Zenject;

namespace GLOW.Scenes.OutpostEnhance.Domain.UseCases
{
    public class GetOutpostEnhanceModelUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstOutpostEnhanceDataRepository MstOutpostEnhanceDataRepository { get; }
        [Inject] IMstArtworkDataRepository MstArtworkDataRepository { get; }

        public OutpostEnhanceUseCaseModel GetOutpostEnhanceModel()
        {
            List<OutpostEnhanceTypeButtonModel> buttons = new List<OutpostEnhanceTypeButtonModel>();

            // userDataからの情報
            var userCoin = GameRepository.GetGameFetch().UserParameterModel.Coin;
            var userOutpostList = GameRepository.GetGameFetchOther().UserOutpostModels;
            var userOutpostEnhanceList = GameRepository.GetGameFetchOther().UserOutpostEnhanceModels;
            // 利用している拠点ID
            var usedOutpostId = userOutpostList.FirstOrDefault(userOutpost => userOutpost.IsUsed)?.MstOutpostId ??
                                new MasterDataId(OutpostDefaultParameterConst.DefaultOutpostId);
            // mstDataからの情報
            var outpostModel = MstOutpostEnhanceDataRepository.GetOutpostModel(usedOutpostId);

            var currentHp = GetCurrentHp();

            foreach (var enhanceModel in outpostModel.EnhancementModels)
            {
                // 現在のレベル取得
                var currentLevel = userOutpostEnhanceList.FirstOrDefault(userOutpostEnhance => userOutpostEnhance.MstOutpostEnhanceId == enhanceModel.Id)?.Level ??
                                   new OutpostEnhanceLevel(1);
                // 最大レベル取得
                var maxLevel = enhanceModel.Levels.OrderByDescending(level => level.Level.Value).First().Level;
                // 次レベルの情報取得
                MstOutpostEnhancementLevelModel nextLevelModel;
                if (currentLevel.Value < maxLevel.Value)
                {
                    nextLevelModel = enhanceModel.Levels.First(level => level.Level.Value == currentLevel.Value + 1);
                }
                else
                {
                    nextLevelModel = enhanceModel.Levels.First(level => level.Level.Value == currentLevel.Value);
                }

                var button = new OutpostEnhanceTypeButtonModel(
                    outpostModel.Id,
                    enhanceModel.Id,
                    nextLevelModel.Id,
                    enhanceModel.Name,
                    nextLevelModel.Description,
                    currentLevel,
                    maxLevel,
                    nextLevelModel.Cost,
                    enhanceModel.Type,
                    OutpostEnhanceIconAssetPath.FromAssetKey(enhanceModel.IconAssetKey)
                );

                buttons.Add(button);
            }

            return new OutpostEnhanceUseCaseModel(userCoin, currentHp, buttons);
        }

        HP GetCurrentHp()
        {
            // デフォルトHP
            var defaultHp = new HP(OutpostDefaultParameterConst.DefaultOutpostHp);

            // 原画ボーナス分のHP
            var gameFetchOther = GameRepository.GetGameFetchOther();
            var userArtworkModels = gameFetchOther.UserArtworkModels;

            var userArtworkIds = userArtworkModels
                .Select(userArtwork => userArtwork.MstArtworkId)
                .ToHashSet();

            var mstArtworks = MstArtworkDataRepository.GetArtworks()
                .Where(mst => userArtworkIds.Contains(mst.Id))
                .ToList();
            var artworkBonus = new HP(mstArtworks.Sum(mst => mst.OutpostAdditionalHp.Value));

            // ゲート強化分のHP
            var userOutpostList = gameFetchOther.UserOutpostModels;
            var userOutpostEnhanceList = gameFetchOther.UserOutpostEnhanceModels;
            var usedOutpostId = userOutpostList.FirstOrDefault(userOutpost => userOutpost.IsUsed)?.MstOutpostId ??
                                new MasterDataId(OutpostDefaultParameterConst.DefaultOutpostId);
            var outpostModel = MstOutpostEnhanceDataRepository.GetOutpostModel(usedOutpostId);
            var enhancementModel = outpostModel.EnhancementModels
                .FirstOrDefault(x => x.Type == OutpostEnhancementType.OutpostHP, MstOutpostEnhancementModel.Empty);
            var currentLevel = userOutpostEnhanceList.FirstOrDefault(userOutpostEnhance => userOutpostEnhance.MstOutpostEnhanceId == enhancementModel.Id)?.Level ??
                               new OutpostEnhanceLevel(1);
            var enhanceHp = enhancementModel.Levels.Find(level => level.Level.Value == currentLevel.Value).EnhanceValue.ToHP();

            // 合算値
            var totalHp = defaultHp + artworkBonus + enhanceHp;

            return totalHp;
        }
    }
}
