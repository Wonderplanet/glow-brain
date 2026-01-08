using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using Zenject;

namespace GLOW.Modules.Tutorial.Domain.UseCases
{
    public class TutorialPartyFormationUseCase
    {
        const int MaxPartyMemberCount = 5;
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IMstCharacterDataRepository MstCharacterDataRepository { get; }
        
        public List<UserDataId> GetUnitsToAddParty()
        {
            // 所持キャラ
            var userUnitModels = GameRepository.GetGameFetchOther().UserUnitModels;
            
            // 編成済みキャラUserDataId
            var partyMember = GameRepository.GetGameFetchOther().UserPartyModels[0].GetUnitList();

            // 所持キャラidから編成済みキャラを除外
            var notAssignedUnitModels = userUnitModels
                .Where(model => !partyMember.Contains(model.UsrUnitId))
                .ToList();
            
            // レア度順にソート
            notAssignedUnitModels = notAssignedUnitModels
                .OrderByDescending(model => MstCharacterDataRepository.GetCharacter(model.MstUnitId).Rarity)
                .ThenBy(model => model.MstUnitId)
                .ToList();
            
            // ソートされたモデルからUserDataIdを取得
            var notAssignedUnitIds = notAssignedUnitModels
                .Select(model => model.UsrUnitId)
                .ToList();

            // 所持キャラ数を取得
            var partyMemberCount = GetPartyMemberCount();
            
            // 編成済みキャラと合わせて5体になるように要素数を制限する
            if (notAssignedUnitIds.Count + partyMemberCount > MaxPartyMemberCount)
            {
                var addMemberCount = MaxPartyMemberCount - partyMemberCount;
                notAssignedUnitIds = notAssignedUnitIds.Take(addMemberCount).ToList();
            }
            
            return notAssignedUnitIds;
        }
        
        int GetPartyMemberCount()
        {
            var party = GameRepository.GetGameFetchOther().UserPartyModels[0].GetUnitList();

            return party.Count(u => !u.IsEmpty());
        }
    }
}