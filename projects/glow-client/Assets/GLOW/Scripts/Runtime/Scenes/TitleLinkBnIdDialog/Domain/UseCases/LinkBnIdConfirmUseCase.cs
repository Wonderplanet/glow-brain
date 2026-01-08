using System;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.TitleLinkBnIdDialog.Domain.Models;
using Zenject;

namespace GLOW.Scenes.TitleLinkBnIdDialog.Domain.UseCases
{
    public class LinkBnIdConfirmUseCase
    {
        [Inject] IUserService UserService { get; }
        [Inject] IGameRepository GameRepository { get; }

        public async UniTask<LinkBnIdConfirmModel> LinkBnIdConfirm(CancellationToken cancellationToken, BnIdCode code)
        {
            var result = await UserService.LinkBnIdConfirm(cancellationToken, code);
            var gameFetchOther = GameRepository.GetGameFetchOther();
            var gameFetch = GameRepository.GetGameFetch();
            var myId = gameFetchOther.UserProfileModel.MyId;
            var bnIdLinkedAt = gameFetchOther.BnIdLinkedAt;
            var bnIdLinkRejectionReasonType = BnIdLinkRejectionReasonType.None;
            // チュートリアル プレイヤー名を設定し終わっているか
            var isEndTutorialSetName = !GameRepository.GetGameFetchOther().TutorialStatus.ShouldSetName();

            bnIdLinkRejectionReasonType = GetBnIdLinkRejectionReasonType(result, isEndTutorialSetName, bnIdLinkedAt, myId);
            // 連携可能かどうか
            var bnIdLinkableFrag =
                CheckOtherLinkUserData(result, myId)
                || CheckNotLinkUserData(result, bnIdLinkedAt, isEndTutorialSetName)
                    ? BnIdLinkableFlag.True : BnIdLinkableFlag.False;
            // 連携したいプレイヤーデータがすでに存在している
            var bnIdLinkedFlag = !result.IsUserEmpty() ? BnIdLinkedFlag.True : BnIdLinkedFlag.False;

            var linkMyId = result.MyId;
            var linkUserName = result.UserName;
            var linkUserLevel = result.UserLevel;

            // 新規登録の場合は現在のユーザー情報を返す
            if (bnIdLinkableFrag && !bnIdLinkedFlag)
            {
                linkMyId = myId;
                linkUserName = gameFetchOther.UserProfileModel.Name;
                linkUserLevel = gameFetch.UserParameterModel.Level;
            }

            return new LinkBnIdConfirmModel(
                linkMyId,
                linkUserName,
                linkUserLevel,
                bnIdLinkableFrag,
                bnIdLinkedFlag,
                bnIdLinkRejectionReasonType,
                code);
        }

        bool CheckOtherLinkUserData(UserLinkBnIdConfirmResultModel result, UserMyId myId)
        {
            // 切り替え先のアカウントが端末のアカウントと異なる場合
            return !result.IsUserEmpty() && result.MyId != myId;
        }

        bool CheckNotLinkUserData(UserLinkBnIdConfirmResultModel result, DateTimeOffset? bnIdLinkedAt, bool isEndTutorialSetName)
        {
            // 切り替え元のアカウントが未連携で、切り替え先のアカウントが未連携の場合
            return result.IsUserEmpty() && isEndTutorialSetName && bnIdLinkedAt == null;
        }

        BnIdLinkRejectionReasonType GetBnIdLinkRejectionReasonType(UserLinkBnIdConfirmResultModel model, bool isEndTutorialSetName, DateTimeOffset? bnIdLinkedAt, UserMyId myId)
        {
            // 切り替え先のアカウントが端末のアカウントと同じ場合
            if (!model.IsUserEmpty() && model.MyId == myId)
            {
                return BnIdLinkRejectionReasonType.AlreadyLinkedWithSameBnId;
            }
            // 切り替え元のアカウントが連携済みで、切り替え先のアカウントが未連携の場合
            else if (model.IsUserEmpty() && isEndTutorialSetName && bnIdLinkedAt != null)
            {
                return BnIdLinkRejectionReasonType.BnIdSwitchingIsNotAllowed;
            }
            // どちらも未連携で、端末のアカウントが未作成の場合
            else if (model.IsUserEmpty() && !isEndTutorialSetName && bnIdLinkedAt == null)
            {
                return BnIdLinkRejectionReasonType.UserDataNotCreated;
            }

            return BnIdLinkRejectionReasonType.None;
        }
    }
}
