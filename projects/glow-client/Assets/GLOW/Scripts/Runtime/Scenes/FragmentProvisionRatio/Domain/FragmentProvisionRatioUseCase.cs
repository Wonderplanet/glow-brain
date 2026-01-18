using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Translators;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.ItemDetail.Domain.Factory;
using GLOW.Scenes.ItemDetail.Domain.Models;
using Zenject;

namespace GLOW.Scenes.FragmentProvisionRatio.Domain
{
    public class FragmentProvisionRatioUseCase
    {
        [Inject] IMstFragmentBoxGroupDataRepository MstFragmentBoxGroupDataRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IMstItemDataRepository MstItemDataRepository { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IItemDetailAvailableLocationModelFactory ItemDetailAvailableLocationModelFactory { get; }

        public FragmentProvisionRatioUseCaseModel GetUseCaseModel(
            MasterDataId mstFragmentBoxGroupId,
            MasterDataId randomFragmentBoxMstItemId)
        {
            var items = CreateProvisionRatioItemModels(mstFragmentBoxGroupId);
            var locationModel = CreateItemDetailAvailableLocationModel(randomFragmentBoxMstItemId);
            return new FragmentProvisionRatioUseCaseModel(items, locationModel);
        }

        IReadOnlyList<FragmentProvisionRatioItemModel> CreateProvisionRatioItemModels(MasterDataId mstFragmentBoxGroupId)
        {
            //MstFragmentBoxGroupから排出一覧を取得
            var boxGroupModels = MstFragmentBoxGroupDataRepository.GetFragmentBoxGroups(mstFragmentBoxGroupId);

            return boxGroupModels
                .Select(b =>
                {
                    var mstItem = MstItemDataRepository.GetItem(b.ItemId);
                    var userItem = GameRepository.GetGameFetchOther().UserItemModels
                        .FirstOrDefault(i => i.MstItemId == b.ItemId, UserItemModel.Empty);

                    var character = MstCharacterDataRepository.GetCharacterByFragmentMstItemId(mstItem.Id);
                    return new FragmentProvisionRatioItemModel(
                        character.Id,
                        ItemModelTranslator.ToItemModel(userItem, mstItem),
                        character.Rarity,
                        mstItem.Name,
                        GetOutputRatio(boxGroupModels.Count)
                    );
                })
                .ToList();
        }

        ItemDetailAvailableLocationModel CreateItemDetailAvailableLocationModel(MasterDataId mstItemId)
        {
            return ItemDetailAvailableLocationModelFactory.Create(ResourceType.Item, mstItemId);
        }

        OutputRatio GetOutputRatio(int itemCount)
        {
            return new OutputRatio((1 / (decimal)itemCount) * 100);
        }
    }
}
