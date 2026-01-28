using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.StaminaRecover.Domain;

namespace GLOW.Core.Domain.Services
{
    public interface IUserService
    {
        UniTask<UserInfoResultModel> Info(CancellationToken cancellationToken);
        UniTask Agree(CancellationToken cancellationToken, int tosVersion, int privacyPolicyVersion, int globalCnsntVersion, int inAppAdvertisementVersion);
        UniTask<UserBuyStaminaDiamondResultModel> BuyStaminaDiamond(CancellationToken cancellationToken);
        UniTask<UserBuyStaminaAdResultModel> BuyStaminaAd(CancellationToken cancellationToken);
        UniTask<UserChangeAvatarResultModel> ChangeAvatar(CancellationToken cancellationToken, MasterDataId mstUnitId);
        UniTask<UserChangeEmblemResultModel> ChangeEmblem(CancellationToken cancellationToken, MasterDataId mstEmblemId);
        UniTask ChangeName(CancellationToken cancellationToken, UserName userName);
        UniTask<UserLinkBnIdConfirmResultModel> LinkBnIdConfirm(CancellationToken cancellationToken, BnIdCode bnIdCode);
        UniTask<UserLinkBnIdResultModel> LinkBnId(CancellationToken cancellationToken, BnIdCode bnIdCode, bool isHome);
        UniTask UnlinkBnId(CancellationToken cancellationToken);
    }
}
