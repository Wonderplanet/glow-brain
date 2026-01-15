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

namespace GLOW.Modules.Tutorial.Domain.UseCases
{
    public class GachaConfirmedApplyUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IPartyService PartyService { get; }
        [Inject] IUserService UserService { get; }
        
        public async UniTask UpdateGachaConfirmedApplyIfNeeds(CancellationToken cancellationToken)
        {
            var gameFetchOther = GameRepository.GetGameFetchOther();
            var userUnits = gameFetchOther.UserUnitModels;
            var uRUnit = GetURUnitIdOrFirst(userUnits);
            
            // 編成情報がない場合はURを1対編成する
            var formedParties = gameFetchOther.UserPartyModels;
            if (formedParties.IsEmpty())
            {
                // URキャラを取得 なければ最初のキャラを取得
                var newPartyModels = CreatePartyModels(uRUnit);
            
                // 編成更新のAPI通信
                var resultParty = await PartyService.Save(cancellationToken, newPartyModels);
                // アバター更新API通信
                var resultChangeAvatar = await UserService.ChangeAvatar(cancellationToken, uRUnit.MstUnitId);

                SavePartyAndProfile(resultParty, resultChangeAvatar);

                return;
            }

            // 編成情報はあるが、URキャラがアバター表示設定されていなければ設定する
            var profileUnit = gameFetchOther.UserProfileModel.MstUnitId;
            if (profileUnit != uRUnit.MstUnitId)
            {
                var resultChangeAvatar = await UserService.ChangeAvatar(cancellationToken, uRUnit.MstUnitId);
                SaveProfile(resultChangeAvatar);
            }
        }
        
        
        UserUnitModel GetURUnitIdOrFirst(IReadOnlyList<UserUnitModel> resultModels)
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
        
        List<UserPartyModel> CreatePartyModels(UserUnitModel userUnitModel)
        {
            var newPartyModels = new List<UserPartyModel>();
            for(var i = 1 ; i <= PartyMemberSlotCount.Max.Value; i++)
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
            // ガシャ結果の保存
            var prevFetchOtherModel = GameRepository.GetGameFetchOther();
            
            // 編成とアバター更新の結果を反映
            var newFetchOtherModel = prevFetchOtherModel with
            {
                UserPartyModels = prevFetchOtherModel.UserPartyModels.Update(partySaveResultModel.Parties),
                UserProfileModel = userChangeAvatarResultModel.UserProfileModel
            };
            
            // 消費物・獲得物のセーブ
            GameManagement.SaveGameFetchOther(newFetchOtherModel);
        }

        void SaveProfile(UserChangeAvatarResultModel userChangeAvatarResultModel)
        {
            // ガシャ結果の保存
            var prevFetchOtherModel = GameRepository.GetGameFetchOther();
            
            // 編成とアバター更新の結果を反映
            var newFetchOtherModel = prevFetchOtherModel with
            {
                UserProfileModel = userChangeAvatarResultModel.UserProfileModel
            };
            
            // 消費物・獲得物のセーブ
            GameManagement.SaveGameFetchOther(newFetchOtherModel);
        }
    }
}