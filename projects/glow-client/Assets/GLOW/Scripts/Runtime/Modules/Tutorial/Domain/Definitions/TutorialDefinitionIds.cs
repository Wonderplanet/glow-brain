using System.Collections.Generic;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Modules.Tutorial.Domain.Definitions
{
    public static class TutorialDefinitionIds
    {
        public static MasterDataId QuestId { get; } = new MasterDataId("tutorial");
        public static MasterDataId Stage1Id { get; } = new MasterDataId("tutorial_1");
        public static MasterDataId Stage2Id { get; } = new MasterDataId("tutorial_2");
        public static MasterDataId Stage3Id { get; } = new MasterDataId("tutorial_3");
        public static List<MasterDataId> StageIds { get; } = new List<MasterDataId>
        {
            Stage1Id,
            Stage2Id,
            Stage3Id,
        };
        public static List<(MasterDataId MstUnitId, UserDataId UserUnitId)> TutorialUnitIds { get; } = new List<(MasterDataId, UserDataId)>
        {
            (new MasterDataId("chara_kai_00002"), new UserDataId("chara_kai_00002")),
            (new MasterDataId("chara_spy_00101"), new UserDataId("chara_spy_00101")),
            (new MasterDataId("chara_dan_00002"), new UserDataId("chara_dan_00002")),
            (new MasterDataId("chara_sur_00101"), new UserDataId("chara_sur_00101")),
            (new MasterDataId("chara_chi_00002"), new UserDataId("chara_chi_00002")),
        };
        
    }
}