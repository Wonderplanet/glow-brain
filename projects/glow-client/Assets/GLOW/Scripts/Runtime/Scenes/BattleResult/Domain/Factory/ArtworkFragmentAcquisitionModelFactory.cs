using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFragment.Domain.Model;
using GLOW.Scenes.ArtworkFragment.Domain.ValueObjects;
using GLOW.Scenes.ArtworkFragmentAcquisition.Domain.Models;
using GLOW.Scenes.InGame.Domain.Models;
using Zenject;

namespace GLOW.Scenes.BattleResult.Domain.Factory
{
    public class ArtworkFragmentAcquisitionModelFactory : IArtworkFragmentAcquisitionModelFactory
    {
        [Inject] IMstArtworkDataRepository MstArtworkDataRepository { get; }
        [Inject] IMstArtworkFragmentDataRepository MstArtworkFragmentDataRepository { get; }

        public IReadOnlyList<ArtworkFragmentAcquisitionModel> CreateArtworkFragmentAcquisitionModels(
            StageEndResultModel stageEndResultModel,
            IReadOnlyList<UserArtworkFragmentModel> beforeUserArtworkFragmentModels)
        {
            var artworkFragmentAcquisitionModels = new List<ArtworkFragmentAcquisitionModel>();

            // 扱いやすいようにデータを整形
            Dictionary<MasterDataId, List<MasterDataId>> artworkFragmentDictionary =
                new Dictionary<MasterDataId, List<MasterDataId>>();
            foreach (var result in stageEndResultModel.UserArtworkFragmentModels)
            {
                if (artworkFragmentDictionary.ContainsKey(result.MstArtworkId))
                {
                    artworkFragmentDictionary[result.MstArtworkId].Add(result.MstArtworkFragmentId);
                }
                else
                {
                    artworkFragmentDictionary.Add(result.MstArtworkId,
                        new List<MasterDataId> { result.MstArtworkFragmentId });
                }
            }

            foreach (var dictionary in artworkFragmentDictionary)
            {
                var artworkData = MstArtworkDataRepository.GetArtwork(dictionary.Key);
                var artworkFragmentData = MstArtworkFragmentDataRepository.GetArtworkFragments(dictionary.Key);
                var userArtworkFragmentData = beforeUserArtworkFragmentModels
                    .Where(d => d.MstArtworkId == dictionary.Key)
                    .ToList();

                var artworkFragmentModel = new List<ArtworkFragmentModel>();
                var artworkFragmentPositions = new List<ArtworkFragmentPositionNum>();
                foreach (var data in artworkFragmentData)
                {
                    artworkFragmentModel.Add(new ArtworkFragmentModel(
                        data.Position,
                        userArtworkFragmentData.Any(d =>
                            d.MstArtworkFragmentId == data.Id && d.MstArtworkId == data.MstArtworkId)));

                    if (dictionary.Key == data.MstArtworkId && dictionary.Value.Any(d => d == data.Id))
                        artworkFragmentPositions.Add(data.Position);
                }

                var artworkPanelModel = new ArtworkPanelModel(
                    ArtworkAssetPath.Create(artworkData.AssetKey),
                    artworkFragmentModel);

                var isCompleted =
                    new ArtworkCompleteFlag(
                        stageEndResultModel.UserArtworkModels.Any(d => d.MstArtworkId == dictionary.Key));

                artworkFragmentAcquisitionModels.Add(new ArtworkFragmentAcquisitionModel(
                    artworkPanelModel,
                    artworkFragmentPositions,
                    artworkData.Name,
                    artworkData.Description,
                    isCompleted,
                    isCompleted ? artworkData.OutpostAdditionalHp : new HP(0)));
            }

            return artworkFragmentAcquisitionModels;
        }

        ArtworkFragmentAcquisitionModel IArtworkFragmentAcquisitionModelFactory.
            CreateArtworkFragmentAcquisitionModel(
                IReadOnlyList<UserArtworkModel> acquiredArtworks,
                MasterDataId acquisitionRewardModelResourceId,
                IReadOnlyList<UserArtworkFragmentModel> beforeUserArtworkFragmentModels)
        {
            var artwork = MstArtworkDataRepository.GetArtwork(acquisitionRewardModelResourceId);
            var mstArtworkId = artwork.Id;

            // 既に完成済みの原画の場合は演出をさせない
            var isAlreadyCompleted = beforeUserArtworkFragmentModels
                .Count(d => d.MstArtworkId == mstArtworkId) == MstArtworkFragmentDataRepository.GetArtworkFragments(mstArtworkId).Count;

            if (isAlreadyCompleted) return ArtworkFragmentAcquisitionModel.Empty;

            var artworkData = MstArtworkDataRepository.GetArtwork(mstArtworkId);
            var artworkFragmentData = MstArtworkFragmentDataRepository.GetArtworkFragments(mstArtworkId);
            var userArtworkFragmentData = beforeUserArtworkFragmentModels
                .Where(d => d.MstArtworkId == mstArtworkId)
                .ToList();

            var artworkFragmentModels = new List<ArtworkFragmentModel>();
            var artworkFragmentPositions = new List<ArtworkFragmentPositionNum>();
            foreach (var data in artworkFragmentData)
            {
                var isAlreadyAcquired = userArtworkFragmentData.Any(d =>
                    d.MstArtworkFragmentId == data.Id && d.MstArtworkId == data.MstArtworkId);

                artworkFragmentModels.Add(new ArtworkFragmentModel(
                    data.Position,
                    isAlreadyAcquired));

                // 既に獲得済みのかけらは除外
                if (!isAlreadyAcquired)
                {
                    artworkFragmentPositions.Add(data.Position);
                }
            }

            var artworkPanelModel = new ArtworkPanelModel(
                ArtworkAssetPath.Create(artworkData.AssetKey),
                artworkFragmentModels);

            var isCompleted = new ArtworkCompleteFlag(acquiredArtworks
                .Any(d => d.MstArtworkId == mstArtworkId));

            return new ArtworkFragmentAcquisitionModel(
                artworkPanelModel,
                artworkFragmentPositions,
                artworkData.Name,
                artworkData.Description,
                isCompleted,
                isCompleted ? artworkData.OutpostAdditionalHp : new HP(0));
        }
    }
}
