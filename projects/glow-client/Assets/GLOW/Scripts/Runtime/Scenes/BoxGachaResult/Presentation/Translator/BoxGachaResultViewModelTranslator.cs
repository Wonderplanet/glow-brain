using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Core.Presentation.Translators;
using GLOW.Core.Presentation.ViewModels;
using GLOW.Scenes.ArtworkFragmentAcquisition.Presentation.Translator;
using GLOW.Scenes.BoxGacha.Domain.Model;
using GLOW.Scenes.BoxGachaResult.Presentation.ViewModel;
using GLOW.Scenes.GachaResult.Presentation;

namespace GLOW.Scenes.BoxGachaResult.Presentation.Translator
{
    public static class BoxGachaResultViewModelTranslator
    {
        public static BoxGachaResultViewModel Translate(BoxGachaDrawModel drawModel)
        {
            var cellViewModels = drawModel.DrawnBoxGachaResultRewards
                .Select(model => new GachaResultCellViewModel(
                    PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(
                        model.BoxGachaReward.PlayerResourceModel),
                    model.IsNewUnitBadge))
                .ToList();
            
            var convertedCellViewModels = drawModel.DrawnBoxGachaResultRewards
                .Select(model =>
                {
                    var preConversionResource = model.BoxGachaReward.PreConversionPlayerResourceModel;
                    return preConversionResource.IsEmpty() ? 
                        PlayerResourceIconViewModel.Empty : 
                        PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(preConversionResource);
                })
                .ToList();

            var avatarViewModels = cellViewModels
                .Where(viewModel => viewModel.IsNewUnitBadge)
                .Select(viewModel => viewModel.PlayerResourceIconViewModel)
                .ToList();

            var existsPreConversionResource = new PreConversionResourceExistenceFlag(
                convertedCellViewModels.Any(viewModel => !viewModel.IsEmpty()));

            var isUnreceivedByResourceOverflowDiscarded = ContainUnreceivedByResourceOverflowDiscarded(
                drawModel.DrawnBoxGachaResultRewards);

            var artworkFragmentAcquisitionViewModels = ArtworkFragmentAcquisitionViewModelTranslator
                .ToTranslate(drawModel.ArtworkFragmentAcquisitionModels);

            return new BoxGachaResultViewModel(
                cellViewModels,
                convertedCellViewModels,
                avatarViewModels,
                existsPreConversionResource,
                isUnreceivedByResourceOverflowDiscarded,
                artworkFragmentAcquisitionViewModels);
        }
        
        static UnreceivedByResourceOverflowDiscardedFlag ContainUnreceivedByResourceOverflowDiscarded(
            IReadOnlyList<BoxGachaDrawResultCellModel> drawnBoxGachaResultRewards)
        {
            return drawnBoxGachaResultRewards.Any(
                model => model.BoxGachaReward.UnreceivedRewardReasonType 
                         == UnreceivedRewardReasonType.ResourceOverflowDiscarded)
                ? UnreceivedByResourceOverflowDiscardedFlag.True
                : UnreceivedByResourceOverflowDiscardedFlag.False;
        }
    }
}