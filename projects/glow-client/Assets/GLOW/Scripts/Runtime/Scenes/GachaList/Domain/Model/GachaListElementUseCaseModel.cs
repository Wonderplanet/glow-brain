using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.GachaContent.Domain.Model;

namespace GLOW.Scenes.GachaList.Domain.Model
{
    public record GachaListElementUseCaseModel(
        GachaFooterBannerUseCaseModel GachaFooterBannerUseCaseModel,
        GachaContentAssetUseCaseModel GachaContentAssetUseCaseModel,
        GachaContentUseCaseModel GachaContentUseCaseModel,
        StepUpGachaContentUseCaseModel StepUpGachaContentUseCaseModel)
    {
        public static GachaListElementUseCaseModel Empty { get; } =
            new(
                GachaFooterBannerUseCaseModel.Empty,
                GachaContentAssetUseCaseModel.Empty,
                GachaContentUseCaseModel.Empty,
                StepUpGachaContentUseCaseModel.Empty);

        // Model直下に引っ越してもよさそうな気もするが、一旦ここに置く
        public MasterDataId OprGachaId => GachaFooterBannerUseCaseModel.OprGachaId;
        public GachaType GachaType => GachaContentUseCaseModel.GachaType;

        public bool IsStepUpGacha()
        {
            return !StepUpGachaContentUseCaseModel.IsEmpty();
        }

        public bool IsEmpty()
        {
            return ReferenceEquals(this, Empty);
        }
    };
}
