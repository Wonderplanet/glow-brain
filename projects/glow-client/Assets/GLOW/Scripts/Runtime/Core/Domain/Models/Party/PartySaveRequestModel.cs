namespace GLOW.Core.Domain.Models.Party
{
    public record PartySaveRequestModel(int PartyNo, string PartyName, string[] UserUnitIds);
}
