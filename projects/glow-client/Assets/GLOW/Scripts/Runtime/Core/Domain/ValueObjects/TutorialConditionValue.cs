using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record TutorialConditionValue(ObscuredString Value)
    {
        public static TutorialConditionValue Empty { get; } = new TutorialConditionValue(string.Empty);

        public UserLevel ToUserLevel()
        {
            if(int.TryParse(Value, out var userLevel))
            {
                return new UserLevel(userLevel);
            }

            return UserLevel.Empty;
        }

        public MasterDataId ToMasterDataId()
        {
            return new MasterDataId(Value);
        }
    }
}
