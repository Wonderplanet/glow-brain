using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Constants;
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
                .Select(model =>
                {
                    // 変換前の情報がある場合はそちらを優先して参照する
                    var targetResource = model.BoxGachaReward.PreConversionPlayerResourceModel.IsEmpty() ? 
                        model.BoxGachaReward.PlayerResourceModel : 
                        model.BoxGachaReward.PreConversionPlayerResourceModel;
                    
                    return new GachaResultCellViewModel(
                        PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(
                            targetResource),
                        model.IsNewUnitBadge);
                })
                .ToList();
            
            var convertedCellViewModels = drawModel.DrawnBoxGachaResultRewards
                .Select(model =>
                {
                    var isExistPreConversionResource = !model.BoxGachaReward.PreConversionPlayerResourceModel.IsEmpty();
                    return isExistPreConversionResource
                        ? PlayerResourceIconViewModelTranslator.ToPlayerResourceIconViewModel(
                            model.BoxGachaReward.PlayerResourceModel)
                        : PlayerResourceIconViewModel.Empty;
                })
                .ToList();

            var avatarViewModels = cellViewModels
                .Where(viewModel => viewModel.IsNewUnitBadge)
                .Select(viewModel => viewModel.PlayerResourceIconViewModel)
                .ToList();

            var existsPreConversionResource = new PreConversionResourceExistenceFlag(
                convertedCellViewModels.Any(viewModel => !viewModel.IsEmpty()));

            var unreceivedRewardReasonTypeByDrawnResult = CreateUnreceivedRewardReasonTypeByDrawnResult(
                drawModel.DrawnBoxGachaResultRewards);

            var artworkFragmentAcquisitionViewModels = ArtworkFragmentAcquisitionViewModelTranslator
                .ToTranslate(drawModel.ArtworkFragmentAcquisitionModels);

            return new BoxGachaResultViewModel(
                cellViewModels,
                convertedCellViewModels,
                avatarViewModels,
                existsPreConversionResource,
                unreceivedRewardReasonTypeByDrawnResult,
                artworkFragmentAcquisitionViewModels);
        }
        
        static IReadOnlyList<UnreceivedRewardReasonType> CreateUnreceivedRewardReasonTypeByDrawnResult(
            IReadOnlyList<BoxGachaDrawResultCellModel> drawnBoxGachaResultRewards)
        {
            return drawnBoxGachaResultRewards
                .Where(model => model.BoxGachaReward.UnreceivedRewardReasonType != UnreceivedRewardReasonType.None)
                .Select(model => model.BoxGachaReward.UnreceivedRewardReasonType)
                .Distinct()
                .ToList();
        }
    }
}