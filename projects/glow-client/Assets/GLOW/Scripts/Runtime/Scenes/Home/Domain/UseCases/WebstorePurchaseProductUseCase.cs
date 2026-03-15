using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Scenes.Home.Domain.UseCases
{
    public class WebstorePurchaseProductUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }

        public WebstorePurchaseFlag HasWebstorePurchaseProduct()
        {
            var gameFetchOther = GameRepository.GetGameFetchOther();
            var webstorePurchaseFlag = gameFetchOther.WebstorePurchaseProductSubIds.Any()
                ? WebstorePurchaseFlag.True
                : WebstorePurchaseFlag.False;

            // 一度表示したら空にする
            var newGameFetchOther = gameFetchOther with
            {
                WebstorePurchaseProductSubIds = new List<MasterDataId>()
            };
            GameManagement.SaveGameFetchOther(newGameFetchOther);

            return webstorePurchaseFlag;
        }
    }
}
