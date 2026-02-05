using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Data.DataStores;
using GLOW.Core.Data.Translators;
using GLOW.Core.Data.Translators.StaminaRecover;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.StaminaRecover.Domain;
using UnityHTTPLibrary;
using WPFramework.Exceptions.Mappers;
using Zenject;

namespace GLOW.Core.Data.Services
{
    public sealed class UserService : IUserService
    {
        [Inject] UserApi UserApi { get; }
        [Inject] IServerErrorExceptionMapper ServerErrorExceptionMapper { get; }

        async UniTask<UserInfoResultModel> IUserService.Info(CancellationToken cancellationToken)
        {
            var data = await UserApi.Info(cancellationToken);
            return UserInfoResultModelTranslator.ToUserInfoResultModel(data);
        }

        async UniTask IUserService.Agree(
            CancellationToken cancellationToken,
            int tosVersion,
            int privacyPolicyVersion,
            int globalConsentVersion,
            int inAppAdvertisementVersion)
        {
            // NOTE: HEADOKなので戻り値は不要
            await UserApi.Agree(cancellationToken, tosVersion, privacyPolicyVersion, globalConsentVersion, inAppAdvertisementVersion);
        }
        async UniTask<UserBuyStaminaDiamondResultModel> IUserService.BuyStaminaDiamond(CancellationToken cancellationToken)
        {
            var data = await UserApi.BuyStaminaDiamond(cancellationToken);
            return UserBuyStaminaDiamondResultTranslator.ToUserBuyStaminaDiamondResultModel(data.UsrParameter,
                data.UsrBuyCount);
        }
        async UniTask<UserBuyStaminaAdResultModel> IUserService.BuyStaminaAd(CancellationToken cancellationToken)
        {
            var data =  await UserApi.BuyStaminaAd(cancellationToken);
            return UserBuyStaminaAdResultTranslator.ToUserBuyStaminaAdResultModel(data.UsrParameter,
                data.UsrBuyCount);
        }

        async UniTask<UserChangeAvatarResultModel> IUserService.ChangeAvatar(
            CancellationToken cancellationToken,
            MasterDataId mstUnitId)
        {
            var data = await UserApi.ChangeAvatar(cancellationToken, mstUnitId.Value);
            return UserChangeAvatarResultDataTranslator.Translate(data);
        }

        async UniTask<UserChangeEmblemResultModel> IUserService.ChangeEmblem(
            CancellationToken cancellationToken,
            MasterDataId mstEmblemId)
        {
            var data = await UserApi.ChangeEmblem(cancellationToken, mstEmblemId.Value);
            return UserChangeEmblemResultDataTranslator.Translate(data);
        }

        async UniTask IUserService.ChangeName(CancellationToken cancellationToken, UserName userName)
        {
            try
            {
                // NOTE: HEADOKなので戻り値は不要
                await UserApi.ChangeName(cancellationToken, userName.Value);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<UserLinkBnIdConfirmResultModel> IUserService.LinkBnIdConfirm(
            CancellationToken cancellationToken,
            BnIdCode bnIdCode)
        {
            try
            {
                var userLinkBnIdResultData = await UserApi.LinkBnidConfirm(cancellationToken, bnIdCode.Value);
                return UserLinkBnIdConfirmResultDataTranslator.Translate(userLinkBnIdResultData);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask<UserLinkBnIdResultModel> IUserService.LinkBnId(
            CancellationToken cancellationToken,
            BnIdCode bnIdCode,
            bool isHome)
        {
            try
            {
                var userLinkBnIdResultData = await UserApi.LinkBnid(cancellationToken, bnIdCode.Value, isHome);
                return UserLinkBnIdResultDataTranslator.Translate(userLinkBnIdResultData);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }

        async UniTask IUserService.UnlinkBnId(CancellationToken cancellationToken)
        {
            try
            {
                await UserApi.UnlinkLinkBnid(cancellationToken);
            }
            catch (ServerErrorException se)
            {
                throw ServerErrorExceptionMapper.Map(se);
            }
        }
    }
}
