using System.Collections.Generic;
using GLOW.Core.Domain.Models.Gacha;
using GLOW.Core.Domain.ValueObjects.Gacha;
using GLOW.Modules.InAppReview.Domain.ValueObject;

namespace GLOW.Scenes.GachaResult.Domain.Model
{
    public record GachaResultUseCaseModel(
        GachaDrawInfoModel GachaDrawInfoModel,
        DrawableFlag DrawableFlag,
        GachaDrawFromContentViewFlag GachaDrawFromContentViewFlag,
        List<GachaResultResourceModel> GachaResultModels,
        List<GachaResultResourceModel> GachaResultConvertedModels,
        List<GachaResultAvatarModel> GachaResultAvatarModels,
        PreConversionResourceExistenceFlag ExistsPreConversionResource,
        InAppReviewFlag IsAppReviewDisplay);
}
