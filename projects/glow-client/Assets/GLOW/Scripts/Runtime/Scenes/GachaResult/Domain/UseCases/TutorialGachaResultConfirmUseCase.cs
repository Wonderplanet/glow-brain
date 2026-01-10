using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Text;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.Extensions;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Modules.Tutorial.Domain.Definitions;
using Zenject;

namespace GLOW.Scenes.GachaResult.Domain.UseCases
{
    public class TutorialGachaResultConfirmUseCase
    {
        [Inject] ITutorialService TutorialService { get; }
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IGameManagement GameManagement { get; }
        [Inject] IPartyService PartyService { get; }
        [Inject] IUserService UserService { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        [Inject] IAcquisitionDisplayedUnitIdsRepository AcquisitionDisplayedUnitIdsRepository { get; }

        public async UniTask ConfirmTutorialGachaResult(CancellationToken cancellationToken)
        {
            // API通信 : ガシャ結果の確定を受け取る
            var result = await TutorialService.GachaConfirm(cancellationToken);
            
            // URキャラを取得 なければ最初のキャラを取得
            var unit = GetURUnitIdOrFirst(result.UserUnitModels);
            var newPartyModels = CreatePartyModels(unit);
            
            // 編成更新のAPI通信
            var resultParty = await PartyService.Save(cancellationToken, newPartyModels);
            
            // アバター更新API通信
            var resultChangeAvatar = await UserService.ChangeAvatar(cancellationToken, unit.MstUnitId);
            
            // ガシャ結果の保存
            var preFetchModel = GameRepository.GetGameFetch();
            var preFetchOtherModel = GameRepository.GetGameFetchOther();
            
            // ユーザーパラメータ・所持アイテムの更新
            var newFetch = preFetchModel with
            {
                UserParameterModel = result.UserParameterModel
            };
            
            var newFetchOtherModel = preFetchOtherModel with
            {
                TutorialStatus = result.TutorialStatusModel,
                UserUnitModels = preFetchOtherModel.UserUnitModels.Update(result.UserUnitModels),
                UserItemModels = preFetchOtherModel.UserItemModels.Update(result.UserItemModels),
                UserPartyModels = preFetchOtherModel.UserPartyModels.Update(resultParty.Parties),
                UserProfileModel = resultChangeAvatar.UserProfileModel
            };
            
            // 受け取ったユニットIDを保存
            AcquisitionDisplayedUnitIdsRepository.SetAcquisitionDisplayedUnitIds(
                newFetchOtherModel.UserUnitModels
                    .Select(model => model.MstUnitId)
                    .Distinct()
                    .ToList());
            
            // 消費物・獲得物のセーブ
            GameManagement.SaveGameUpdateAndFetch(newFetch, newFetchOtherModel);
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
    }
}
