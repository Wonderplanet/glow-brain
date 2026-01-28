using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ItemDetail.Domain.Factory;
using GLOW.Scenes.ItemDetail.Domain.Models;
using Zenject;

namespace GLOW.Scenes.ItemBox.Domain.UseCases
{
    public class GetFragmentLineupUseCase
    {
        [Inject] IMstFragmentBoxGroupDataRepository MstFragmentBoxGroupDataRepository { get; }
        [Inject] IMstFragmentBoxDataRepository MstFragmentBoxDataRepository { get; }
        [Inject] IMstItemDataRepository MstItemDataRepository { get; }
        [Inject] IItemDetailAvailableLocationModelFactory AvailableLocationModelFactory { get; }

        public GetFragmentLineupUseCaseModel GetFragmentLineup(MasterDataId mstItemId, MasterDataId selectedFragmentId)
        {
            var lineupItemModelList = CreateLineupItemModelList(mstItemId, selectedFragmentId);
            var availableLocationModel = CreateAvailableLocationModel(mstItemId);

            return new GetFragmentLineupUseCaseModel(lineupItemModelList, availableLocationModel);
        }

        IReadOnlyList<MstItemModel> CreateLineupItemModelList(MasterDataId mstItemId, MasterDataId selectedFragmentId)
        {
            var fragmentBox = MstFragmentBoxDataRepository.GetFragmentBox(mstItemId);
            var fragmentBoxGroups =
                MstFragmentBoxGroupDataRepository.GetFragmentBoxGroups(fragmentBox.FragmentBoxGroupId);

            // 選択されたフラグメントIDが指定されている場合、フィルタリング
            if (!selectedFragmentId.IsEmpty())
            {
                fragmentBoxGroups = fragmentBoxGroups
                    .Where(group => group.ItemId == selectedFragmentId)
                    .ToList();
            }

            var allItems = fragmentBoxGroups
                .Select(group => MstItemDataRepository.GetItem(group.ItemId))
                .ToList();

            return allItems;
        }

        ItemDetailAvailableLocationModel CreateAvailableLocationModel(MasterDataId mstItemId)
        {
            var fragmentBox = MstFragmentBoxDataRepository.GetFragmentBox(mstItemId);
            return AvailableLocationModelFactory.Create(ResourceType.Item, fragmentBox.ItemId);
        }

        public MasterDataId GetMstFragmentGroupId(MasterDataId itemId)
        {
            return MstFragmentBoxDataRepository.GetFragmentBox(itemId).FragmentBoxGroupId;
        }
    }
}
