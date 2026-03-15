using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Text;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Party;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using Zenject;

namespace GLOW.Modules.Tutorial.Domain.Applier
{
    /// <summary>
    /// チュートリアルガチャ確定後のパーティ編成とアバター設定を行う
    /// </summary>
    public class TutorialGachaConfirmedApplier : ITutorialGachaConfirmedApplier
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IPartyService PartyService { get; }
        [Inject] IUserService UserService { get; }

        /// <summary>
        /// パーティ編成とアバター設定を必要に応じて実行する
        /// </summary>
        public async UniTask ApplyPartyAndAvatarIfNeeds(CancellationToken cancellationToken)
        {
            var gameFetchOther = GameRepository.GetGameFetchOther();
            var userUnits = gameFetchOther.UserUnitModels;
            var urUnit = GetUrUnitIdOrFirst(userUnits);

            // 編成情報がない場合はURを1対編成する
            var formedParties = gameFetchOther.UserPartyModels;
            if (formedParties.IsEmpty())
            {
                var newPartyModels = CreatePartyModels(urUnit);

                // 編成更新のAPI通信
                var resultParty = await PartyService.Save(cancellationToken, newPartyModels);
                // アバター更新API通信
                var resultChangeAvatar = await UserService.ChangeAvatar(cancellationToken, urUnit.MstUnitId);

                SavePartyAndProfile(resultParty, resultChangeAvatar);

                return;
            }

            // 編成情報はあるが、URキャラがアバター表示設定されていなければ設定する
            var profileUnit = gameFetchOther.UserProfileModel.MstUnitId;
            if (profileUnit != urUnit.MstUnitId)
            {
                var resultChangeAvatar = await UserService.ChangeAvatar(cancellationToken, urUnit.MstUnitId);
                SaveProfile(resultChangeAvatar);
            }
        }

        /// <summary>
        /// パーティ編成とアバター設定を実行し、結果を保存する
        /// </summary>
        public async UniTask<(PartySaveResultModel, UserChangeAvatarResultModel)> ApplyPartyAndAvatar(
            CancellationToken cancellationToken,
            UserUnitModel urUnit)
        {
            var newPartyModels = CreatePartyModels(urUnit);

            // 編成更新のAPI通信
            var resultParty = await PartyService.Save(cancellationToken, newPartyModels);
            // アバター更新API通信
            var resultChangeAvatar = await UserService.ChangeAvatar(cancellationToken, urUnit.MstUnitId);

            return (resultParty, resultChangeAvatar);
        }

        public UserUnitModel GetUrUnitIdOrFirst(IReadOnlyList<UserUnitModel> resultModels)
        {
            foreach (var userUnit in resultModels)
            {
                var mstUnit = MstCharacterDataRepository.GetCharacter(userUnit.MstUnitId);
                if (mstUnit != null && mstUnit.Rarity == Rarity.UR)
                {
                    return userUnit;
                }
            }

            return resultModels.First();
        }

        public List<UserPartyModel> CreatePartyModels(UserUnitModel userUnitModel)
        {
            var newPartyModels = new List<UserPartyModel>();
            for (var i = 1; i <= PartyMemberSlotCount.Max.Value; i++)
            {
                var partyModel = UserPartyModel.Empty with
                {
                    PartyNo = new PartyNo(i),
                    PartyName = new PartyName(ZString.Format("party{0}", i)),
                    Unit1 = userUnitModel.UsrUnitId
                };
                newPartyModels.Add(partyModel);
            }

            return newPartyModels;
        }

        void SavePartyAndProfile(
            PartySaveResultModel partySaveResultModel,
            UserChangeAvatarResultModel userChangeAvatarResultModel)
        {
            var prevFetchOtherModel = GameRepository.GetGameFetchOther();

            var newFetchOtherModel = prevFetchOtherModel with
            {
                UserPartyModels = prevFetchOtherModel.UserPartyModels.Update(partySaveResultModel.Parties),
                UserProfileModel = userChangeAvatarResultModel.UserProfileModel
            };

            GameManagement.SaveGameFetchOther(newFetchOtherModel);
        }

        void SaveProfile(UserChangeAvatarResultModel userChangeAvatarResultModel)
        {
            var prevFetchOtherModel = GameRepository.GetGameFetchOther();

            var newFetchOtherModel = prevFetchOtherModel with
            {
                UserProfileModel = userChangeAvatarResultModel.UserProfileModel
            };

            GameManagement.SaveGameFetchOther(newFetchOtherModel);
        }
    }
}




