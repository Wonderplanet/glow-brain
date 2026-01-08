using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Domain.ValueObjects
{
    public record TutorialFunctionName(ObscuredString Value)
    {
        public static TutorialFunctionName Empty { get; } = new TutorialFunctionName(string.Empty);

        public MasterDataId ToMasterDataId()
        {
            return new MasterDataId(Value);
        }
    }
}
