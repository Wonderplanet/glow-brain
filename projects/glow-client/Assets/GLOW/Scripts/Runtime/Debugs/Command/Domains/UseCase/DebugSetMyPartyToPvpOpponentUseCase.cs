using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Models.Pvp;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.OutpostEnhance;
using Zenject;

namespace GLOW.Debugs.Command.Domains.UseCase
{
    public class DebugSetMyPartyToPvpOpponentUseCase
    {
        [Inject] IGameRepository GameRepository { get; }
        [Inject] IPartyCacheRepository PartyCacheRepository { get; }
        [Inject] IPvpSelectedOpponentStatusCacheRepository PvpSelectedOpponentStatusCacheRepository { get; }
        
        public void SetUpPvpVsMyParty()
        {
            var gameFetchOther = GameRepository.GetGameFetchOther();
            var userUnits = gameFetchOther.UserUnitModels;
            var myParty = PartyCacheRepository.GetCurrentPartyModel();
            var myPartyUserUnitIdSet = myParty.GetUnitList().ToHashSet();
            var myPartyPvpUnits = userUnits
                .Where(unit => myPartyUserUnitIdSet.Contains(unit.UsrUnitId))
                .Select(unit => new PvpUnitModel(
                    unit.MstUnitId, 
                    unit.Level, 
                    unit.Rank,
                    unit.Grade))
                .ToList();
            
            // PVPの対戦相手を仮で設定
            PvpSelectedOpponentStatusCacheRepository.SetOpponentStatus(
                OpponentPvpStatusModel.Empty with
                {
                    PvpUnits = myPartyPvpUnits,
                    UsrOutpostEnhancements = new List<UserOutpostEnhanceModel>()
                    {
                        UserOutpostEnhanceModel.Empty with
                        {
                            // Vビーム攻撃力
                            MstOutpostId = new MasterDataId("outpost_1"),
                            MstOutpostEnhanceId = new MasterDataId("enhance_1_1"),
                            Level = new OutpostEnhanceLevel(3),
                        },
                        UserOutpostEnhanceModel.Empty with
                        {
                            // リーダーPの増加スピードアップ
                            MstOutpostId = new MasterDataId("outpost_1"),
                            MstOutpostEnhanceId = new MasterDataId("enhance_1_3"),
                            Level = new OutpostEnhanceLevel(2),
                        },
                    },
                    UsrEncyclopediaEffects = new List<PvpEncyclopediaEffectModel>()
                    {
                        PvpEncyclopediaEffectModel.Empty with
                        {
                            MstEncyclopediaEffectId = new MasterDataId("unit_encyclopedia_effect_5")
                        },
                        PvpEncyclopediaEffectModel.Empty with
                        {
                            MstEncyclopediaEffectId = new MasterDataId("unit_encyclopedia_effect_10")
                        },
                        PvpEncyclopediaEffectModel.Empty with
                        {
                            MstEncyclopediaEffectId = new MasterDataId("unit_encyclopedia_effect_15")
                        },
                    }
                });
        }
    }
}